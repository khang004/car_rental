<?php
// filepath: d:\xampp\htdocs\xe\admin\don_thue_xe\update_status.php
include("../admin_common.php");
check_admin_login();

// Kiểm tra tham số đầu vào
$don_id = intval($_GET['id'] ?? 0);
$new_status = trim($_GET['status'] ?? '');

if ($don_id <= 0) {
    js_alert('ID đơn thuê không hợp lệ!');
    redirect_to('admin/don_thue_xe/index.php');
}

// Danh sách trạng thái hợp lệ
$valid_statuses = ['da_xac_nhan', 'dang_thue', 'da_tra_xe', 'da_huy'];
if (!in_array($new_status, $valid_statuses)) {
    js_alert('Trạng thái không hợp lệ!');
    redirect_to('admin/don_thue_xe/index.php');
}

// Lấy thông tin đơn thuê hiện tại
$sql_current = "
    SELECT dt.*, x.bien_so_xe, x.ten_xe, kh.ho_ten as ten_khach_hang 
    FROM don_thue_xe dt 
    JOIN xe x ON dt.xe_id = x.id 
    JOIN khach_hang kh ON dt.khach_hang_id = kh.id
    WHERE dt.id = ?
";
$current_order = db_select($sql_current, [$don_id]);

if (empty($current_order)) {
    js_alert('Không tìm thấy đơn thuê!');
    redirect_to('admin/don_thue_xe/index.php');
}

$current_order = $current_order[0];
$current_admin = get_current_admin();

// Kiểm tra logic chuyển trạng thái
$current_status = $current_order['trang_thai'];
$allowed_transitions = [
    'cho_xac_nhan' => ['da_xac_nhan', 'da_huy'],
    'da_xac_nhan' => ['dang_thue', 'da_huy'],
    'dang_thue' => ['da_tra_xe'],
    'da_tra_xe' => [], // Không thể chuyển từ trạng thái này
    'da_huy' => [] // Không thể chuyển từ trạng thái này
];

if (!isset($allowed_transitions[$current_status]) || 
    !in_array($new_status, $allowed_transitions[$current_status])) {
    js_alert('Không thể chuyển từ trạng thái hiện tại sang trạng thái mới!');
    redirect_to('admin/don_thue_xe/index.php');
}

// Xác định trạng thái xe tương ứng
$xe_status = '';
switch ($new_status) {
    case 'da_xac_nhan':
        // Xe vẫn sẵn sàng cho đến khi bắt đầu thuê
        $xe_status = 'san_sang';
        break;
    case 'dang_thue':
        // Xe đang được thuê
        $xe_status = 'dang_thue';
        break;
    case 'da_tra_xe':
        // Xe đã trả, sẵn sàng cho thuê tiếp
        $xe_status = 'san_sang';
        break;
    case 'da_huy':
        // Xe trở về trạng thái sẵn sàng
        $xe_status = 'san_sang';
        break;
}

// Cập nhật trạng thái đơn thuê
$sql_update_order = "
    UPDATE don_thue_xe 
    SET trang_thai = ?, 
        nguoi_xac_nhan_id = ?, 
        ngay_cap_nhat = CURRENT_TIMESTAMP 
    WHERE id = ?
";
$result_order = db_execute($sql_update_order, [$new_status, $current_admin['id'], $don_id]);

if ($result_order) {
    // Cập nhật trạng thái xe nếu cần
    if (!empty($xe_status)) {
        $sql_update_xe = "UPDATE xe SET trang_thai = ? WHERE id = ?";
        $result_xe = db_execute($sql_update_xe, [$xe_status, $current_order['xe_id']]);
        
        if (!$result_xe) {
            // Nếu không cập nhật được trạng thái xe, ghi log nhưng vẫn thành công
            error_log("Không thể cập nhật trạng thái xe ID: " . $current_order['xe_id']);
        }
    }
    
    // Thông báo thành công
    $status_text = get_order_status_text($new_status);
    $message = "Đã cập nhật đơn thuê #{$current_order['ma_don_hang']} thành trạng thái: {$status_text}";
    js_alert($message);
    
} else {
    js_alert('Có lỗi xảy ra khi cập nhật trạng thái đơn thuê!');
}

// Chuyển hướng về trang danh sách đơn thuê
redirect_to('admin/don_thue_xe/index.php');
?>