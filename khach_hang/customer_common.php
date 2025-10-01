<?php
include("../include/common.php");

// Khởi tạo session cho khách hàng
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập khách hàng
function check_customer_login() {
    if (!isset($_SESSION['customer_id'])) {
        redirect_to('khach_hang/login.php');
        exit;
    }
    
    // Kiểm tra khách hàng có tồn tại và đang hoạt động không
    $sql = "SELECT id FROM khach_hang WHERE id = ? AND trang_thai = 1";
    $customer = db_select($sql, [$_SESSION['customer_id']]);
    
    if (empty($customer)) {
        // Xóa session không hợp lệ và chuyển về login
        session_destroy();
        js_alert("Tài khoản không tồn tại hoặc đã bị khóa!");
        redirect_to('khach_hang/login.php');
        exit;
    }
}

// Đăng xuất khách hàng
function customer_logout() {
    session_destroy();
    redirect_to('khach_hang/login.php');
}

// Lấy thông tin khách hàng đang đăng nhập
function get_current_customer() {
    if (isset($_SESSION['customer_id'])) {
        $sql = "SELECT * FROM khach_hang WHERE id = ? AND trang_thai = 1";
        $customer = db_select($sql, [$_SESSION['customer_id']]);
        return !empty($customer) ? $customer[0] : null;
    }
    return null;
}

// Format tiền tệ VNĐ
function format_money($amount) {
    return number_format($amount, 0, ',', '.') . ' VNĐ';
}

// Format ngày tháng dd/mm/yyyy
function format_date($date) {
    if (empty($date) || $date == '0000-00-00') return '';
    return date('d/m/Y', strtotime($date));
}

// Format ngày giờ dd/mm/yyyy hh:mm
function format_datetime($datetime) {
    if (empty($datetime) || $datetime == '0000-00-00 00:00:00') return '';
    return date('d/m/Y H:i', strtotime($datetime));
}

// Tạo mã đơn thuê tự động
function generate_order_code() {
    return 'XE' . date('Ymd') . rand(1000, 9999);
}

// Lấy tên trạng thái đơn thuê bằng tiếng Việt
function get_order_status_text($status) {
    $status_list = [
        'cho_xac_nhan' => 'Chờ xác nhận',
        'da_xac_nhan' => 'Đã xác nhận',
        'dang_thue' => 'Đang thuê',
        'da_tra_xe' => 'Đã trả xe',
        'da_huy' => 'Đã hủy',
        'qua_han' => 'Quá hạn'
    ];
    return isset($status_list[$status]) ? $status_list[$status] : $status;
}

// Lấy CSS class cho trạng thái đơn thuê
function get_order_status_class($status) {
    $class_list = [
        'cho_xac_nhan' => 'warning',
        'da_xac_nhan' => 'info',
        'dang_thue' => 'primary',
        'da_tra_xe' => 'success',
        'da_huy' => 'danger',
        'qua_han' => 'dark'
    ];
    return isset($class_list[$status]) ? $class_list[$status] : 'secondary';
}

// Kiểm tra xe có sẵn trong khoảng thời gian không
function is_xe_available_for_customer($xe_id, $ngay_bat_dau, $ngay_ket_thuc) {
    $sql = "SELECT COUNT(*) as count FROM don_thue_xe 
            WHERE xe_id = ? 
            AND trang_thai IN ('da_xac_nhan', 'dang_thue')
            AND (
                (ngay_bat_dau <= ? AND ngay_ket_thuc >= ?) OR
                (ngay_bat_dau <= ? AND ngay_ket_thuc >= ?) OR
                (ngay_bat_dau >= ? AND ngay_ket_thuc <= ?)
            )";
    
    $params = [$xe_id, $ngay_bat_dau, $ngay_bat_dau, $ngay_ket_thuc, $ngay_ket_thuc, $ngay_bat_dau, $ngay_ket_thuc];
    
    $result = db_select($sql, $params);
    return $result[0]['count'] == 0;
}

// Tính tổng tiền thuê xe
function calculate_rental_total($gia_thue_theo_ngay, $ngay_bat_dau, $ngay_ket_thuc) {
    $start_date = new DateTime($ngay_bat_dau);
    $end_date = new DateTime($ngay_ket_thuc);
    $interval = $start_date->diff($end_date);
    $so_ngay = $interval->days;
    
    if ($so_ngay <= 0) $so_ngay = 1; // Tối thiểu 1 ngày
    
    return $gia_thue_theo_ngay * $so_ngay;
}

// Tính tiền cọc (30% tổng tiền)
function calculate_deposit($tong_tien) {
    return $tong_tien * 0.3;
}

// Kiểm tra thời gian có thể hủy đơn (24h trước ngày thuê)
function can_cancel_order($ngay_bat_dau) {
    $now = new DateTime();
    $start_date = new DateTime($ngay_bat_dau);
    $interval = $now->diff($start_date);
    
    // Có thể hủy nếu còn hơn 24 giờ
    return $interval->days > 0 || ($interval->days == 0 && $interval->h >= 24);
}
?>