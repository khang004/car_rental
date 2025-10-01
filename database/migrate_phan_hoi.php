<?php
// File migration để thêm cột phan_hoi vào bảng danh_gia
// Chạy file này một lần để sửa lỗi "unknown column phan_hoi"

include(__DIR__ . "/../include/common.php");

try {
    echo "<h2>Migration: Thêm cột phan_hoi vào bảng danh_gia</h2>";
    
    // Kiểm tra cột phan_hoi có tồn tại không
    $check_sql = "SHOW COLUMNS FROM danh_gia LIKE 'phan_hoi'";
    $result = db_select($check_sql, []);
    
    if (empty($result)) {
        echo "<p>Cột phan_hoi không tồn tại. Đang thêm...</p>";
        
        // Thêm cột phan_hoi
        $add_column_sql = "ALTER TABLE danh_gia ADD COLUMN phan_hoi TEXT COMMENT 'Phản hồi từ quản trị' AFTER noi_dung";
        $add_result = db_execute($add_column_sql, []);
        
        if ($add_result) {
            echo "<p style='color: green;'>✅ Đã thêm cột phan_hoi thành công!</p>";
        } else {
            echo "<p style='color: red;'>❌ Lỗi khi thêm cột phan_hoi!</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Cột phan_hoi đã tồn tại.</p>";
    }
    
    // Hiển thị cấu trúc bảng hiện tại
    echo "<h3>Cấu trúc bảng danh_gia hiện tại:</h3>";
    $describe_sql = "DESCRIBE danh_gia";
    $columns = db_select($describe_sql, []);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>Migration hoàn thành!</strong> Bây giờ bạn có thể sử dụng chức năng phản hồi đánh giá.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Vui lòng kiểm tra kết nối database và quyền truy cập.</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>