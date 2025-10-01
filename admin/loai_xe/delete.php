<?php
include("../admin_common.php");
check_admin_login();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Kiểm tra xem loại xe có đang được sử dụng không
    $sql_check = "SELECT COUNT(*) as count FROM xe WHERE loai_xe_id = ?";
    $check_result = db_select($sql_check, [$id]);
    
    if ($check_result[0]['count'] > 0) {
        js_alert("Không thể xóa loại xe này vì đang có xe sử dụng!");
    } else {
        // Xóa loại xe
        $sql = "DELETE FROM loai_xe WHERE id = ?";
        $result = db_execute($sql, [$id]);
        
        if ($result) {
            js_alert("Xóa loại xe thành công!");
        } else {
            js_alert("Có lỗi xảy ra, vui lòng thử lại!");
        }
    }
}

redirect_to("admin/loai_xe/");
?>