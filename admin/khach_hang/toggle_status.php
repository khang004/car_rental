<?php
include("../admin_common.php");
check_admin_login();

$id = intval($_GET['id'] ?? 0);
$status = intval($_GET['status'] ?? 1);

if ($id > 0) {
    // Cập nhật trạng thái khách hàng
    $sql = "UPDATE khach_hang SET trang_thai = ? WHERE id = ?";
    $result = db_execute($sql, [$status, $id]);
    
    if ($result) {
        $action = $status == 1 ? "mở khóa" : "khóa";
        js_alert("Đã $action tài khoản khách hàng thành công!");
    } else {
        js_alert("Có lỗi xảy ra, vui lòng thử lại!");
    }
}

redirect_to("admin/khach_hang/");
?>