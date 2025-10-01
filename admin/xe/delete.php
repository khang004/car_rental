<?php
include("../admin_common.php");
check_admin_login();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Kiểm tra xem xe có đang được thuê không
    $sql_check = "SELECT COUNT(*) as count FROM don_thue_xe WHERE xe_id = ? AND trang_thai IN ('cho_xac_nhan', 'da_xac_nhan', 'dang_thue')";
    $check_result = db_select($sql_check, [$id]);
    
    if ($check_result[0]['count'] > 0) {
        js_alert("Không thể xóa xe này vì đang có đơn thuê!");
    } else {
        // Lấy thông tin xe để xóa hình ảnh
        $sql_xe = "SELECT hinh_anh FROM xe WHERE id = ?";
        $xe_info = db_select($sql_xe, [$id]);
        
        if (!empty($xe_info) && !empty($xe_info[0]['hinh_anh'])) {
            remove_file($xe_info[0]['hinh_anh']);
        }
        
        // Xóa xe
        $sql = "DELETE FROM xe WHERE id = ?";
        $result = db_execute($sql, [$id]);
        
        if ($result) {
            js_alert("Xóa xe thành công!");
        } else {
            js_alert("Có lỗi xảy ra, vui lòng thử lại!");
        }
    }
}

redirect_to("admin/xe/");
?>