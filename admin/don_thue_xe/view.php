<?php
// filepath: d:\xampp\htdocs\xe\admin\don_thue_xe\view.php
include("../admin_common.php");
check_admin_login();

// Lấy ID đơn thuê
$don_id = intval($_GET['id'] ?? 0);

if ($don_id <= 0) {
    js_alert('ID đơn thuê không hợp lệ!');
    redirect_to('admin/don_thue_xe/index.php');
}

// Lấy thông tin chi tiết đơn thuê
$sql = "
    SELECT dt.*, 
           x.ten_xe, x.bien_so_xe, x.hinh_anh, x.mau_sac, x.nam_san_xuat, x.so_cho_ngoi,
           lx.ten_loai,
           kh.ho_ten, kh.email, kh.so_dien_thoai, kh.dia_chi,
           qtv.ho_ten as ten_nguoi_xac_nhan
    FROM don_thue_xe dt
    JOIN xe x ON dt.xe_id = x.id
    JOIN loai_xe lx ON x.loai_xe_id = lx.id
    JOIN khach_hang kh ON dt.khach_hang_id = kh.id
    LEFT JOIN quan_tri_vien qtv ON dt.nguoi_xac_nhan_id = qtv.id
    WHERE dt.id = ?
";

$don_thue = db_select($sql, [$don_id]);

if (empty($don_thue)) {
    js_alert('Không tìm thấy đơn thuê!');
    redirect_to('admin/don_thue_xe/index.php');
}

$don_thue = $don_thue[0];

// Lấy đánh giá của khách hàng (nếu có)
$sql_review = "SELECT * FROM danh_gia WHERE don_thue_xe_id = ?";
$review = db_select($sql_review, [$don_id]);
$has_review = !empty($review);
if ($has_review) {
    $review = $review[0];
}

include("../header.php");
?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Quản lý đơn thuê xe</a></li>
                <li class="breadcrumb-item active">Chi tiết đơn</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-contract"></i> Chi tiết đơn thuê xe</h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Thông tin cơ bản đơn thuê -->
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
                        <p><strong>Thời gian thuê:</strong><br>
                           <span class="text-primary">
                               <?= format_date($don_thue['ngay_bat_dau']) ?> - <?= format_date($don_thue['ngay_ket_thuc']) ?>
                           </span>
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
                           <span class="text-success fs-5"><?= format_money($don_thue['tong_tien']) ?></span>
                        </p>
                        <p><strong>Tiền cọc:</strong> 
                           <span class="text-warning"><?= format_money($don_thue['tien_coc']) ?></span>
                        </p>
                        <p><strong>Trạng thái:</strong>
                           <span class="badge bg-<?= get_order_status_class($don_thue['trang_thai']) ?> fs-6">
                               <?= get_order_status_text($don_thue['trang_thai']) ?>
                           </span>
                        </p>
                        <p><strong>Người xác nhận:</strong>
                           <?php if (!empty($don_thue['ten_nguoi_xac_nhan'])): ?>
                               <?= htmlentities($don_thue['ten_nguoi_xac_nhan']) ?>
                           <?php else: ?>
                               <span class="text-muted">Chưa xác nhận</span>
                           <?php endif; ?>
                        </p>
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
            <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="fas fa-car fa-3x text-muted"></i>
                </div>
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
                <?php if (!empty($don_thue['so_cho_ngoi'])): ?>
                    <p class="mb-0"><strong>Số chỗ:</strong> <?= intval($don_thue['so_cho_ngoi']) ?> chỗ</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Thông tin khách hàng -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-user"></i> Thông tin khách hàng</h6>
            </div>
            <div class="card-body">
                <p><strong>Họ tên:</strong> <?= htmlentities($don_thue['ho_ten']) ?></p>
                <p><strong>Email:</strong> <?= htmlentities($don_thue['email']) ?></p>
                <?php if (!empty($don_thue['so_dien_thoai'])): ?>
                    <p><strong>Số điện thoại:</strong> 
                       <a href="tel:<?= htmlentities($don_thue['so_dien_thoai']) ?>">
                           <?= htmlentities($don_thue['so_dien_thoai']) ?>
                       </a>
                    </p>
                <?php endif; ?>
                <?php if (!empty($don_thue['dia_chi'])): ?>
                    <p><strong>Địa chỉ:</strong> <?= htmlentities($don_thue['dia_chi']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Địa điểm nhận trả -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> Địa điểm nhận/trả xe</h6>
            </div>
            <div class="card-body">
                <p><strong>Địa điểm nhận xe:</strong><br>
                   <?php if (!empty($don_thue['dia_diem_nhan'])): ?>
                       <?= htmlentities($don_thue['dia_diem_nhan']) ?>
                   <?php else: ?>
                       <span class="text-muted">Chưa có thông tin</span>
                   <?php endif; ?>
                </p>
                
                <p><strong>Địa điểm trả xe:</strong><br>
                   <?php if (!empty($don_thue['dia_diem_tra'])): ?>
                       <?= htmlentities($don_thue['dia_diem_tra']) ?>
                   <?php else: ?>
                       <span class="text-muted">Chưa có thông tin</span>
                   <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Ghi chú và lý do hủy -->
<?php if (!empty($don_thue['ghi_chu']) || !empty($don_thue['ly_do_huy'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-sticky-note"></i> Ghi chú</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($don_thue['ghi_chu'])): ?>
                    <p><strong>Ghi chú từ khách hàng:</strong><br>
                       <?= nl2br(htmlentities($don_thue['ghi_chu'])) ?>
                    </p>
                <?php endif; ?>
                
                <?php if (!empty($don_thue['ly_do_huy'])): ?>
                    <div class="alert alert-danger">
                        <strong>Lý do hủy đơn:</strong><br>
                        <?= nl2br(htmlentities($don_thue['ly_do_huy'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Đánh giá khách hàng -->
<?php if ($has_review): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-star"></i> Đánh giá từ khách hàng</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <strong>Điểm đánh giá:</strong>
                    <div>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= $review['diem_danh_gia'] ? 'text-warning' : 'text-muted' ?>"></i>
                        <?php endfor; ?>
                        (<?= $review['diem_danh_gia'] ?>/5)
                    </div>
                </div>
                <p><strong>Bình luận:</strong><br>
                   <?= nl2br(htmlentities($review['binh_luan'])) ?>
                </p>
                <small class="text-muted">
                    Đánh giá ngày <?= format_datetime($review['ngay_danh_gia']) ?>
                </small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Thao tác quản lý -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h6>Thao tác quản lý</h6>
                
                <?php if ($don_thue['trang_thai'] == 'cho_xac_nhan'): ?>
                    <a href="update_status.php?id=<?= $don_thue['id'] ?>&status=da_xac_nhan" 
                       class="btn btn-success me-2"
                       onclick="return confirm('Xác nhận đơn thuê này?')">
                        <i class="fas fa-check"></i> Xác nhận đơn
                    </a>
                    <a href="update_status.php?id=<?= $don_thue['id'] ?>&status=da_huy" 
                       class="btn btn-danger me-2"
                       onclick="return confirm('Hủy đơn thuê này?')">
                        <i class="fas fa-times"></i> Hủy đơn
                    </a>
                    
                <?php elseif ($don_thue['trang_thai'] == 'da_xac_nhan'): ?>
                    <a href="update_status.php?id=<?= $don_thue['id'] ?>&status=dang_thue" 
                       class="btn btn-primary me-2"
                       onclick="return confirm('Bắt đầu cho thuê xe?')">
                        <i class="fas fa-play"></i> Bắt đầu thuê
                    </a>
                    
                <?php elseif ($don_thue['trang_thai'] == 'dang_thue'): ?>
                    <a href="update_status.php?id=<?= $don_thue['id'] ?>&status=da_tra_xe" 
                       class="btn btn-warning me-2"
                       onclick="return confirm('Xác nhận đã trả xe?')">
                        <i class="fas fa-flag-checkered"></i> Hoàn thành
                    </a>
                <?php endif; ?>
                
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Danh sách đơn thuê
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
    .btn, .breadcrumb, .card-header, nav {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

<?php include("../footer.php"); ?>