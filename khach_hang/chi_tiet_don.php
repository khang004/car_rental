<?php
// filepath: d:\xampp\htdocs\xe\khach_hang\chi_tiet_don.php
include("customer_common.php");
check_customer_login();

// Lấy ID đơn thuê
$don_id = intval($_GET['id'] ?? 0);

if ($don_id <= 0) {
    js_alert('ID đơn thuê không hợp lệ!');
    redirect_to('khach_hang/don_thue.php');
}

// Lấy thông tin chi tiết đơn thuê
$sql = "
    SELECT dt.*, 
           x.ten_xe, x.bien_so_xe, x.hinh_anh, x.mau_sac, x.nam_san_xuat, x.so_cho_ngoi,
           lx.ten_loai,
           kh.ho_ten, kh.email, kh.so_dien_thoai,
           qtv.ho_ten as ten_nguoi_xac_nhan
    FROM don_thue_xe dt
    JOIN xe x ON dt.xe_id = x.id
    JOIN loai_xe lx ON x.loai_xe_id = lx.id
    JOIN khach_hang kh ON dt.khach_hang_id = kh.id
    LEFT JOIN quan_tri_vien qtv ON dt.nguoi_xac_nhan_id = qtv.id
    WHERE dt.id = ? AND dt.khach_hang_id = ?
";

$don_thue = db_select($sql, [$don_id, $_SESSION['customer_id']]);

if (empty($don_thue)) {
    js_alert('Không tìm thấy đơn thuê hoặc bạn không có quyền xem!');
    redirect_to('khach_hang/don_thue.php');
}

$don_thue = $don_thue[0];

// Lấy đánh giá nếu có
$sql_review = "SELECT * FROM danh_gia WHERE don_thue_xe_id = ?";
$review = db_select($sql_review, [$don_id]);
$has_review = !empty($review);
if ($has_review) {
    $review = $review[0];
}

include("header.php");
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="don_thue.php">Đơn thuê của tôi</a></li>
                <li class="breadcrumb-item active">Chi tiết đơn</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-contract"></i> Chi tiết đơn thuê xe</h2>
            <a href="don_thue.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Thông tin đơn hàng -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin đơn thuê</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Mã đơn hàng:</strong> <?= htmlentities($don_thue['ma_don_hang']) ?></p>
                        <p><strong>Ngày đặt:</strong> <?= format_datetime($don_thue['ngay_dat']) ?></p>
                        <p><strong>Thời gian thuê:</strong> 
                           <?= format_date($don_thue['ngay_bat_dau']) ?> - <?= format_date($don_thue['ngay_ket_thuc']) ?>
                        </p>
                        <p><strong>Số ngày thuê:</strong> 
                           <?php
                           $start = new DateTime($don_thue['ngay_bat_dau']);
                           $end = new DateTime($don_thue['ngay_ket_thuc']);
                           $days = $start->diff($end)->days;
                           echo $days > 0 ? $days : 1;
                           ?> ngày
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Tổng tiền:</strong> 
                           <span class="text-primary fs-5"><?= format_money($don_thue['tong_tien']) ?></span>
                        </p>
                        <p><strong>Tiền cọc:</strong> 
                           <span class="text-warning"><?= format_money($don_thue['tien_coc']) ?></span>
                        </p>
                        <p><strong>Trạng thái:</strong>
                           <span class="badge bg-<?= get_order_status_class($don_thue['trang_thai']) ?> fs-6">
                               <?= get_order_status_text($don_thue['trang_thai']) ?>
                           </span>
                        </p>
                        <?php if (!empty($don_thue['ten_nguoi_xac_nhan'])): ?>
                            <p><strong>Người xác nhận:</strong> <?= htmlentities($don_thue['ten_nguoi_xac_nhan']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Thông tin xe -->
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-car"></i> Thông tin xe</h6>
            </div>
            <?php if (!empty($don_thue['hinh_anh'])): ?>
                <img src="<?= upload($don_thue['hinh_anh']) ?>" class="card-img-top" 
                     style="height: 200px; object-fit: cover;" alt="<?= htmlentities($don_thue['ten_xe']) ?>">
            <?php endif; ?>
            <div class="card-body">
                <h6><?= htmlentities($don_thue['ten_xe']) ?></h6>
                <p class="mb-1"><strong>Loại xe:</strong> <?= htmlentities($don_thue['ten_loai']) ?></p>
                <p class="mb-1"><strong>Biển số:</strong> <?= htmlentities($don_thue['bien_so_xe']) ?></p>
                <?php if (!empty($don_thue['mau_sac'])): ?>
                    <p class="mb-1"><strong>Màu sắc:</strong> <?= htmlentities($don_thue['mau_sac']) ?></p>
                <?php endif; ?>
                <?php if (!empty($don_thue['nam_san_xuat'])): ?>
                    <p class="mb-1"><strong>Năm sản xuất:</strong> <?= $don_thue['nam_san_xuat'] ?></p>
                <?php endif; ?>
                <p class="mb-0"><strong>Số chỗ:</strong> <?= intval($don_thue['so_cho_ngoi']) ?> chỗ</p>
            </div>
        </div>
    </div>
</div>

<!-- Địa điểm nhận trả -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> Địa điểm nhận xe</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($don_thue['dia_diem_nhan'])): ?>
                    <p class="mb-0"><?= htmlentities($don_thue['dia_diem_nhan']) ?></p>
                <?php else: ?>
                    <p class="text-muted mb-0">Chưa có thông tin</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-map-marked-alt"></i> Địa điểm trả xe</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($don_thue['dia_diem_tra'])): ?>
                    <p class="mb-0"><?= htmlentities($don_thue['dia_diem_tra']) ?></p>
                <?php else: ?>
                    <p class="text-muted mb-0">Chưa có thông tin</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Ghi chú -->
<?php if (!empty($don_thue['ghi_chu']) || !empty($don_thue['ly_do_huy'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-sticky-note"></i> Ghi chú</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($don_thue['ghi_chu'])): ?>
                    <p><strong>Ghi chú:</strong> <?= htmlentities($don_thue['ghi_chu']) ?></p>
                <?php endif; ?>
                <?php if (!empty($don_thue['ly_do_huy'])): ?>
                    <div class="alert alert-danger">
                        <strong>Lý do hủy:</strong> <?= htmlentities($don_thue['ly_do_huy']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Đánh giá -->
<?php if ($don_thue['trang_thai'] == 'da_tra_xe'): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-star"></i> Đánh giá dịch vụ</h6>
            </div>
            <div class="card-body">
                <?php if ($has_review): ?>
                    <div class="alert alert-success">
                        <div class="d-flex justify-content-between mb-2">
                            <strong>Đánh giá của bạn:</strong>
                            <div>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $review['diem_danh_gia'] ? 'text-warning' : 'text-muted' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="mb-1"><?= htmlentities($review['binh_luan']) ?></p>
                        <small class="text-muted">Đánh giá ngày <?= format_datetime($review['ngay_danh_gia']) ?></small>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <p class="text-muted">Bạn chưa đánh giá dịch vụ này</p>
                        <a href="danh_gia.php?id=<?= $don_thue['id'] ?>" class="btn btn-warning">
                            <i class="fas fa-star"></i> Đánh giá ngay
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Thao tác -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6>Thao tác</h6>
                
                <?php if ($don_thue['trang_thai'] == 'cho_xac_nhan' && can_cancel_order($don_thue['ngay_bat_dau'])): ?>
                    <a href="huy_don.php?id=<?= $don_thue['id'] ?>" class="btn btn-danger me-2"
                       onclick="return confirm('Bạn có chắc chắn muốn hủy đơn này?')">
                        <i class="fas fa-times"></i> Hủy đơn
                    </a>
                <?php endif; ?>
                
                <a href="don_thue.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Danh sách đơn thuê
                </a>
                
                <a href="xe.php" class="btn btn-primary">
                    <i class="fas fa-car"></i> Thuê xe khác
                </a>
                
                <button onclick="window.print()" class="btn btn-info">
                    <i class="fas fa-print"></i> In đơn
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CSS cho in ấn -->
<style>
@media print {
    .btn, .breadcrumb, .card-header {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

<?php include("footer.php"); ?>