<?php

// Sửa đường dẫn tới file common.php
include(__DIR__ . "/../include/common.php");

// Khởi tạo session cho admin
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập admin
function check_admin_login() {
    if (!isset($_SESSION['admin_id'])) {
        redirect_to('admin/login.php');
    }
}

// Kiểm tra quyền admin (chỉ admin mới được truy cập)
function check_admin_permission() {
    if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
        js_alert('Bạn không có quyền truy cập chức năng này!');
        redirect_to('admin/index.php');
    }
}

// Đăng xuất admin
function admin_logout() {
    session_destroy();
    redirect_to('admin/login.php');
}

// Lấy thông tin admin đang đăng nhập
function get_current_admin() {
    if (isset($_SESSION['admin_id'])) {
        $sql = "SELECT * FROM quan_tri_vien WHERE id = ? AND trang_thai = 1";
        $admin = db_select($sql, [$_SESSION['admin_id']]);
        return !empty($admin) ? $admin[0] : null;
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

// Lấy tên trạng thái xe bằng tiếng Việt
function get_xe_status_text($status) {
    $status_list = [
        'san_sang' => 'Sẵn sàng',
        'dang_thue' => 'Đang thuê', 
        'bao_tri' => 'Bảo trì',
        'ngung_hoat_dong' => 'Ngừng hoạt động'
    ];
    return isset($status_list[$status]) ? $status_list[$status] : $status;
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

// Lấy CSS class cho trạng thái xe
function get_xe_status_class($status) {
    $class_list = [
        'san_sang' => 'success',
        'dang_thue' => 'warning', 
        'bao_tri' => 'info',
        'ngung_hoat_dong' => 'danger'
    ];
    return isset($class_list[$status]) ? $class_list[$status] : 'secondary';
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

// Lấy danh sách trạng thái xe cho select option
function get_xe_status_options($selected = '') {
    $options = [
        'san_sang' => 'Sẵn sàng',
        'dang_thue' => 'Đang thuê', 
        'bao_tri' => 'Bảo trì',
        'ngung_hoat_dong' => 'Ngừng hoạt động'
    ];
    
    $html = '<option value="">-- Chọn trạng thái --</option>';
    foreach ($options as $value => $text) {
        $selected_attr = ($value == $selected) ? 'selected' : '';
        $html .= "<option value='$value' $selected_attr>$text</option>";
    }
    return $html;
}

// Lấy danh sách trạng thái đơn thuê cho select option
function get_order_status_options($selected = '') {
    $options = [
        'cho_xac_nhan' => 'Chờ xác nhận',
        'da_xac_nhan' => 'Đã xác nhận',
        'dang_thue' => 'Đang thuê',
        'da_tra_xe' => 'Đã trả xe',
        'da_huy' => 'Đã hủy',
        'qua_han' => 'Quá hạn'
    ];
    
    $html = '<option value="">-- Chọn trạng thái --</option>';
    foreach ($options as $value => $text) {
        $selected_attr = ($value == $selected) ? 'selected' : '';
        $html .= "<option value='$value' $selected_attr>$text</option>";
    }
    return $html;
}

// Kiểm tra xe có đang được thuê không
function is_xe_available($xe_id, $ngay_bat_dau, $ngay_ket_thuc, $don_thue_id = null) {
    $sql = "SELECT COUNT(*) as count FROM don_thue_xe 
            WHERE xe_id = ? 
            AND trang_thai IN ('da_xac_nhan', 'dang_thue')
            AND (
                (ngay_bat_dau <= ? AND ngay_ket_thuc >= ?) OR
                (ngay_bat_dau <= ? AND ngay_ket_thuc >= ?) OR
                (ngay_bat_dau >= ? AND ngay_ket_thuc <= ?)
            )";
    
    $params = [$xe_id, $ngay_bat_dau, $ngay_bat_dau, $ngay_ket_thuc, $ngay_ket_thuc, $ngay_bat_dau, $ngay_ket_thuc];
    
    // Nếu đang sửa đơn thuê, loại trừ đơn hiện tại
    if ($don_thue_id) {
        $sql .= " AND id != ?";
        $params[] = $don_thue_id;
    }
    
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

// Lấy thống kê cho dashboard
function get_dashboard_stats() {
    $stats = [];
    
    // Tổng số xe
    $result = db_select("SELECT COUNT(*) as count FROM xe");
    $stats['tong_xe'] = $result[0]['count'];
    
    // Xe đang cho thuê
    $result = db_select("SELECT COUNT(*) as count FROM xe WHERE trang_thai = 'dang_thue'");
    $stats['xe_dang_thue'] = $result[0]['count'];
    
    // Tổng đơn hàng hôm nay
    $result = db_select("SELECT COUNT(*) as count FROM don_thue_xe WHERE DATE(ngay_dat) = CURDATE()");
    $stats['don_hang_hom_nay'] = $result[0]['count'];
    
    // Doanh thu tháng này
    $result = db_select("SELECT COALESCE(SUM(tong_tien), 0) as total FROM don_thue_xe 
                        WHERE trang_thai = 'da_tra_xe' 
                        AND MONTH(ngay_dat) = MONTH(CURDATE()) 
                        AND YEAR(ngay_dat) = YEAR(CURDATE())");
    $stats['doanh_thu_thang'] = $result[0]['total'];
    
    // Tổng khách hàng
    $result = db_select("SELECT COUNT(*) as count FROM khach_hang WHERE trang_thai = 1");
    $stats['tong_khach_hang'] = $result[0]['count'];
    
    return $stats;
}
?>