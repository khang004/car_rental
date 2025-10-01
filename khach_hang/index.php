<?php
include("customer_common.php");

// Lấy thống kê
$sql_stats = "
    SELECT 
        (SELECT COUNT(*) FROM xe WHERE trang_thai = 'san_sang') as xe_san_sang,
        (SELECT COUNT(*) FROM loai_xe) as tong_loai_xe,
        (SELECT COUNT(*) FROM khach_hang WHERE trang_thai = 1) as tong_khach_hang
";
$stats = db_select($sql_stats)[0];

// Lấy xe nổi bật (được thuê nhiều nhất)
$sql_xe_hot = "
    SELECT x.*, lx.ten_loai, COUNT(dt.id) as so_lan_thue
    FROM xe x
    JOIN loai_xe lx ON x.loai_xe_id = lx.id
    LEFT JOIN don_thue_xe dt ON x.id = dt.xe_id AND dt.trang_thai = 'da_tra_xe'
    WHERE x.trang_thai = 'san_sang'
    GROUP BY x.id
    ORDER BY so_lan_thue DESC, x.ngay_tao DESC
    LIMIT 6
";
$xe_hot_list = db_select($sql_xe_hot);

// Lấy đánh giá mới nhất
$sql_reviews = "
    SELECT dg.*, kh.ho_ten, x.ten_xe
    FROM danh_gia dg
    JOIN khach_hang kh ON dg.khach_hang_id = kh.id
    JOIN don_thue_xe dt ON dg.don_thue_xe_id = dt.id
    JOIN xe x ON dt.xe_id = x.id
    ORDER BY dg.ngay_danh_gia DESC
    LIMIT 4
";
$reviews_list = db_select($sql_reviews);

include("header.php");
?>

<!-- Hero Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="bg-primary text-white rounded p-5 text-center">
            <h1 class="display-4 mb-3">
                <i class="fas fa-car"></i> Chào mừng đến với XeDeep
            </h1>
            <p class="lead mb-4">Dịch vụ thuê xe uy tín, chất lượng cao với giá cả hợp lý</p>
            
            <?php if (!isset($_SESSION['customer_id'])): ?>
                <div class="mb-4">
                    <a href="register.php" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-user-plus"></i> Đăng ký ngay
                    </a>
                    <a href="xe.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-car"></i> Xem danh sách xe
                    </a>
                </div>
            <?php else: ?>
                <div class="mb-4">
                    <a href="xe.php" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-car"></i> Thuê xe ngay
                    </a>
                    <a href="don_thue.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-list"></i> Đơn thuê của tôi
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Thống kê -->
<div class="row mb-5">
    <div class="col-md-4 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-car fa-3x text-primary mb-3"></i>
                <h3 class="text-primary"><?= number_format($stats['xe_san_sang']) ?></h3>
                <p class="mb-0">Xe sẵn sàng</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-list fa-3x text-success mb-3"></i>
                <h3 class="text-success"><?= number_format($stats['tong_loai_xe']) ?></h3>
                <p class="mb-0">Loại xe đa dạng</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <i class="fas fa-users fa-3x text-info mb-3"></i>
                <h3 class="text-info"><?= number_format($stats['tong_khach_hang']) ?></h3>
                <p class="mb-0">Khách hàng tin tưởng</p>
            </div>
        </div>
    </div>
</div>

<!-- Xe nổi bật -->
<div class="row mb-5">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-star text-warning"></i> Xe nổi bật</h2>
    </div>
    
    <?php if (empty($xe_hot_list)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Chưa có xe nào.
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($xe_hot_list as $xe): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($xe['hinh_anh'])): ?>
                        <img src="<?= upload($xe['hinh_anh']) ?>" class="card-img-top" 
                             style="height: 180px; object-fit: cover;" alt="<?= htmlentities($xe['ten_xe']) ?>">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                            <i class="fas fa-car fa-2x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlentities($xe['ten_xe']) ?></h5>
                        <p class="text-muted mb-2">
                            <i class="fas fa-tag"></i> <?= htmlentities($xe['ten_loai']) ?> - 
                            <strong><?= htmlentities($xe['bien_so_xe']) ?></strong>
                        </p>
                        
                        <div class="mb-3">
                            <?php if ($xe['so_lan_thue'] > 0): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-fire"></i> Đã thuê <?= $xe['so_lan_thue'] ?> lần
                                </span>
                            <?php endif; ?>
                            <span class="badge bg-success">
                                <i class="fas fa-users"></i> <?= $xe['so_cho_ngoi'] ?> chỗ
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-primary fs-6">
                                    <?= format_money($xe['gia_thue_theo_ngay']) ?>
                                </strong>
                                <small class="text-muted">/ngày</small>
                            </div>
                            
                            <?php if (isset($_SESSION['customer_id'])): ?>
                                <a href="dat_xe.php?id=<?= $xe['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-calendar-plus"></i> Đặt xe
                                </a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-sm btn-outline-primary">
                                    Đăng nhập
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Đánh giá khách hàng -->
<?php if (!empty($reviews_list)): ?>
<div class="row mb-5">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-comments text-primary"></i> Đánh giá từ khách hàng</h2>
    </div>
    
    <?php foreach ($reviews_list as $review): ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <strong><?= htmlentities($review['ho_ten']) ?></strong>
                        <div>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $review['diem_danh_gia'] ? 'text-warning' : 'text-muted' ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-car"></i> <?= htmlentities($review['ten_xe']) ?>
                    </p>
                    <p class="mb-2"><?= htmlentities($review['binh_luan']) ?></p>
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> <?= format_datetime($review['ngay_danh_gia']) ?>
                    </small>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Call to action -->
<div class="row">
    <div class="col-12">
        <div class="bg-light rounded p-4 text-center">
            <h3><i class="fas fa-phone"></i> Liên hệ hỗ trợ</h3>
            <p class="mb-3">Hotline: <strong>1900-xxxx</strong> | Email: <strong>info@xedeep.com</strong></p>
            <a href="xe.php" class="btn btn-primary btn-lg">
                <i class="fas fa-car"></i> Thuê xe ngay
            </a>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>