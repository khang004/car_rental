<?php
// Bắt đầu output buffering để tránh lỗi header
ob_start();

include(__DIR__ . "/../admin_common.php");

// Xóa bất kỳ output nào trước đó
ob_clean();

// Đặt header cho JSON response
header('Content-Type: application/json; charset=utf-8');

// Kiểm tra đăng nhập admin
try {
    check_admin_login();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Không có quyền truy cập: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Chỉ xử lý POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Phương thức không được phép'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Debug: Log thông tin request
error_log("POST data received: " . print_r($_POST, true));

// Lấy dữ liệu từ form
$review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
$phan_hoi = isset($_POST['phan_hoi']) ? trim($_POST['phan_hoi']) : '';

// Debug log
error_log("Processing review_id: $review_id, phan_hoi length: " . strlen($phan_hoi));

// Validate dữ liệu
$errors = [];

if ($review_id <= 0) {
    $errors[] = 'ID đánh giá không hợp lệ';
}

if (empty($phan_hoi)) {
    $errors[] = 'Vui lòng nhập nội dung phản hồi';
}

if (strlen($phan_hoi) > 1000) {
    $errors[] = 'Nội dung phản hồi không được vượt quá 1000 ký tự';
}

// Kiểm tra đánh giá có tồn tại
if (empty($errors)) {
    // Kiểm tra cột phan_hoi có tồn tại không
    try {
        $check_column_sql = "SHOW COLUMNS FROM danh_gia LIKE 'phan_hoi'";
        $column_exists = db_select($check_column_sql, []);
        
        if (empty($column_exists)) {
            // Thêm cột phan_hoi nếu chưa tồn tại
            $add_column_sql = "ALTER TABLE danh_gia ADD COLUMN phan_hoi TEXT COMMENT 'Phản hồi từ quản trị' AFTER noi_dung";
            db_execute($add_column_sql, []);
            error_log("Added phan_hoi column to danh_gia table");
        }
        
        // Kiểm tra đánh giá có tồn tại
        $check_sql = "SELECT id, phan_hoi FROM danh_gia WHERE id = ?";
        $check_result = db_select($check_sql, [$review_id]);
        
        if (empty($check_result)) {
            $errors[] = 'Không tìm thấy đánh giá này';
        } elseif (!empty($check_result[0]['phan_hoi'])) {
            $errors[] = 'Đánh giá này đã được phản hồi rồi';
        }
    } catch (Exception $e) {
        error_log("Error checking/adding phan_hoi column: " . $e->getMessage());
        $errors[] = 'Lỗi kiểm tra cấu trúc bảng: ' . $e->getMessage();
    }
}

// Nếu có lỗi, trả về JSON error
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(', ', $errors)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Debug: Kiểm tra kết nối database
    if (!function_exists('db_execute')) {
        throw new Exception('Hàm db_execute không tồn tại');
    }
    
    // Kiểm tra lại cột phan_hoi trước khi update
    $check_column_sql = "SHOW COLUMNS FROM danh_gia LIKE 'phan_hoi'";
    $column_check = db_select($check_column_sql, []);
    
    if (empty($column_check)) {
        // Cố gắng thêm cột phan_hoi
        try {
            $add_column_sql = "ALTER TABLE danh_gia ADD COLUMN phan_hoi TEXT COMMENT 'Phản hồi từ quản trị' AFTER noi_dung";
            db_execute($add_column_sql, []);
            error_log("Successfully added phan_hoi column to danh_gia table");
        } catch (Exception $e) {
            error_log("Failed to add phan_hoi column: " . $e->getMessage());
            throw new Exception('Cấu trúc database chưa đúng. Vui lòng chạy script migration để thêm cột phan_hoi.');
        }
    }
    
    // Cập nhật phản hồi
    $update_sql = "UPDATE danh_gia SET phan_hoi = ? WHERE id = ?";
    error_log("Executing SQL: $update_sql with params: " . print_r([$phan_hoi, $review_id], true));
    
    $result = db_execute($update_sql, [$phan_hoi, $review_id]);
    
    if ($result) {
        // Kiểm tra xem có thực sự cập nhật được không
        try {
            $verify_sql = "SELECT phan_hoi FROM danh_gia WHERE id = ?";
            $verify_result = db_select($verify_sql, [$review_id]);
            
            if (!empty($verify_result) && isset($verify_result[0]['phan_hoi']) && $verify_result[0]['phan_hoi'] === $phan_hoi) {
                // Log thành công cho debugging
                error_log("Reply successful for review ID: $review_id");
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Phản hồi đã được gửi thành công',
                    'review_id' => $review_id
                ], JSON_UNESCAPED_UNICODE);
            } else {
                error_log("Database update failed - verification failed for review ID: $review_id");
                echo json_encode([
                    'success' => false,
                    'message' => 'Không thể xác nhận việc cập nhật. Vui lòng kiểm tra lại.'
                ], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            // Nếu verify fail, vẫn coi như thành công vì update đã return true
            error_log("Verification failed but update succeeded: " . $e->getMessage());
            echo json_encode([
                'success' => true,
                'message' => 'Phản hồi đã được gửi thành công',
                'review_id' => $review_id,
                'note' => 'Không thể xác nhận nhưng cập nhật đã thành công'
            ], JSON_UNESCAPED_UNICODE);
        }
    } else {
        // Log lỗi cho debugging
        error_log("db_execute returned false for review ID: $review_id");
        
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi lưu phản hồi. Vui lòng thử lại.'
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    // Log lỗi chi tiết
    error_log("Exception in reply.php: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
    
    $error_message = $e->getMessage();
    
    // Nếu lỗi liên quan đến column không tồn tại, đưa ra hướng dẫn
    if (strpos($error_message, 'phan_hoi') !== false || strpos($error_message, 'Unknown column') !== false) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi cấu trúc database: Cột phan_hoi không tồn tại. Vui lòng chạy script migration.',
            'solution' => 'Chạy file database/add_phan_hoi_column.sql hoặc liên hệ admin để cập nhật database.'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi hệ thống: ' . $error_message
        ], JSON_UNESCAPED_UNICODE);
    }
}
?>