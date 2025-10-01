<?php
// filepath: d:\xampp\htdocs\xe\admin\index.php
include("admin_common.php");
check_admin_login();

// Thống kê tổng quan
$sql_stats = "
    SELECT 
        (SELECT COUNT(*) FROM xe WHERE trang_thai != 'ngung_hoat_dong') as tong_xe,
        (SELECT COUNT(*) FROM xe WHERE trang_thai = 'san_sang') as xe_san_sang,
        (SELECT COUNT(*) FROM xe WHERE trang_thai = 'dang_thue') as xe_dang_thue,
        (SELECT COUNT(*) FROM don_thue_xe WHERE trang_thai = 'cho_xac_nhan') as don_cho_xac_nhan,
        (SELECT COUNT(*) FROM don_thue_xe WHERE trang_thai = 'dang_thue') as don_dang_thue,
        (SELECT COUNT(*) FROM khach_hang WHERE trang_thai = 1) as tong_khach_hang,
        (SELECT COALESCE(SUM(tong_tien), 0) FROM don_thue_xe WHERE trang_thai IN ('da_tra_xe', 'dang_thue')) as doanh_thu_thang,
        (SELECT COUNT(*) FROM don_thue_xe WHERE DATE(ngay_dat) = CURDATE()) as don_hom_nay
";

try {
    $stats = db_select($sql_stats);
    if (!empty($stats)) {
        $stats = $stats[0];
    } else {
        // Dữ liệu mặc định nếu không lấy được thống kê
        $stats = [
            'tong_xe' => 0,
            'xe_san_sang' => 0,
            'xe_dang_thue' => 0,
            'don_cho_xac_nhan' => 0,
            'don_dang_thue' => 0,
            'tong_khach_hang' => 0,
            'doanh_thu_thang' => 0,
            'don_hom_nay' => 0
        ];
    }
} catch (Exception $e) {
    // Xử lý lỗi và tạo dữ liệu mặc định
    $stats = [
        'tong_xe' => 0,
        'xe_san_sang' => 0,
        'xe_dang_thue' => 0,
        'don_cho_xac_nhan' => 0,
        'don_dang_thue' => 0,
        'tong_khach_hang' => 0,
        'doanh_thu_thang' => 0,
        'don_hom_nay' => 0
    ];
}

// Đơn thuê mới nhất
$sql_orders = "
    SELECT dt.*, kh.ho_ten as ten_khach_hang, x.bien_so_xe, x.ten_xe 
    FROM don_thue_xe dt
    JOIN khach_hang kh ON dt.khach_hang_id = kh.id
    JOIN xe x ON dt.xe_id = x.id
    ORDER BY dt.ngay_dat DESC 
    LIMIT 5
";

try {
    $recent_orders = db_select($sql_orders);
} catch (Exception $e) {
    $recent_orders = [];
}

include("header.php");
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
        <p class="text-muted">Tổng quan hệ thống quản lý thuê xe XeDeep</p>
    </div>
</div>

<!-- Thống kê tổng quan -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Tổng số xe
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($stats['tong_xe']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-car fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Xe sẵn sàng
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($stats['xe_san_sang']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Đơn chờ xác nhận
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($stats['don_cho_xac_nhan']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Doanh thu tháng
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= format_money($stats['doanh_thu_thang']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Thống kê bổ sung -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Xe đang thuê
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($stats['xe_dang_thue']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                            Tổng khách hàng
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($stats['tong_khach_hang']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-dark shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                            Đơn hôm nay
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($stats['don_hom_nay']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Đơn đang thuê
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($stats['don_dang_thue']) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Đơn thuê mới nhất -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-list"></i> Đơn thuê xe mới nhất
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Mã đơn hàng</th>
                                <th>Khách hàng</th>
                                <th>Xe</th>
                                <th>Ngày đặt</th>
                                <th>Ngày thuê</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_orders)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Chưa có đơn thuê nào</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlentities($order['ma_don_hang']) ?></strong>
                                        </td>
                                        <td><?= htmlentities($order['ten_khach_hang']) ?></td>
                                        <td>
                                            <strong><?= htmlentities($order['bien_so_xe']) ?></strong><br>
                                            <small class="text-muted"><?= htmlentities($order['ten_xe']) ?></small>
                                        </td>
                                        <td><small><?= format_datetime($order['ngay_dat']) ?></small></td>
                                        <td><small><?= format_date($order['ngay_bat_dau']) ?></small></td>
                                        <td><strong><?= format_money($order['tong_tien']) ?></strong></td>
                                        <td>
                                            <span class="badge bg-<?= get_order_status_class($order['trang_thai']) ?>">
                                                <?= get_order_status_text($order['trang_thai']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="don_thue_xe/index.php?search=<?= htmlentities($order['ma_don_hang']) ?>" 
                                               class="btn btn-sm btn-primary" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="text-center mt-3">
                    <a href="don_thue_xe/" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Xem tất cả đơn thuê
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}
.border-left-secondary {
    border-left: 0.25rem solid #858796 !important;
}
.border-left-dark {
    border-left: 0.25rem solid #5a5c69 !important;
}
.text-xs {
    font-size: 0.7rem;
}
.text-gray-800 {
    color: #5a5c69 !important;
}
.text-gray-300 {
    color: #dddfeb !important;
}
</style>

<?php include("footer.php"); ?>