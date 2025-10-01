<?php
include("customer_common.php");

// Lấy tham số tìm kiếm
$search = trim($_GET['search'] ?? '');
$loai_xe_id = intval($_GET['loai_xe_id'] ?? 0);
$gia_tu = floatval($_GET['gia_tu'] ?? 0);
$gia_den = floatval($_GET['gia_den'] ?? 0);
$ngay_thue = $_GET['ngay_thue'] ?? '';
$ngay_tra = $_GET['ngay_tra'] ?? '';

// Xây dựng câu truy vấn
$where_conditions = ["x.trang_thai = 'san_sang'"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(x.ten_xe LIKE ? OR x.bien_so_xe LIKE ? OR lx.ten_loai LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($loai_xe_id > 0) {
    $where_conditions[] = "x.loai_xe_id = ?";
    $params[] = $loai_xe_id;
}

if ($gia_tu > 0) {
    $where_conditions[] = "x.gia_thue_theo_ngay >= ?";
    $params[] = $gia_tu;
}

if ($gia_den > 0) {
    $where_conditions[] = "x.gia_thue_theo_ngay <= ?";
    $params[] = $gia_den;
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Lấy danh sách xe
$sql = "
    SELECT x.*, lx.ten_loai 
    FROM xe x
    JOIN loai_xe lx ON x.loai_xe_id = lx.id
    $where_clause
    ORDER BY x.ngay_tao DESC
";
$xe_list = db_select($sql, $params);

// Lấy danh sách loại xe để filter
$sql_loai_xe = "SELECT * FROM loai_xe ORDER BY ten_loai";
$loai_xe_list = db_select($sql_loai_xe);

include("header.php");
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-car"></i> Danh sách xe cho thuê</h2>
        <p class="text-muted">Chọn xe phù hợp với nhu cầu của bạn</p>
    </div>
</div>

<!-- Bộ lọc tìm kiếm -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" class="form-control" name="search" 
                               value="<?= htmlentities($search) ?>" 
                               placeholder="Tên xe, biển số...">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Loại xe</label>
                        <select class="form-select" name="loai_xe_id">
                            <option value="">Tất cả</option>
                            <?php foreach ($loai_xe_list as $loai): ?>
                                <option value="<?= $loai['id'] ?>" <?= $loai_xe_id == $loai['id'] ? 'selected' : '' ?>>
                                    <?= htmlentities($loai['ten_loai']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Giá từ (VNĐ)</label>
                        <input type="number" class="form-control" name="gia_tu" 
                               value="<?= $gia_tu ?: '' ?>" placeholder="0">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Giá đến (VNĐ)</label>
                        <input type="number" class="form-control" name="gia_den" 
                               value="<?= $gia_den ?: '' ?>" placeholder="0">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                            <a href="xe.php" class="btn btn-secondary">
                                <i class="fas fa-refresh"></i> Làm mới
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Danh sách xe -->
<div class="row">
    <?php if (empty($xe_list)): ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> Không tìm thấy xe nào phù hợp với tiêu chí tìm kiếm.
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($xe_list as $xe): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($xe['hinh_anh'])): ?>
                        <img src="<?= upload($xe['hinh_anh']) ?>" class="card-img-top" 
                             style="height: 200px; object-fit: cover;" alt="<?= htmlentities($xe['ten_xe']) ?>">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-car fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlentities($xe['ten_xe']) ?></h5>
                        <p class="text-muted mb-2">
                            <i class="fas fa-tag"></i> <?= htmlentities($xe['ten_loai']) ?> - 
                            <strong><?= htmlentities($xe['bien_so_xe']) ?></strong>
                        </p>
                        
                        <?php if (!empty($xe['mo_ta'])): ?>
                            <p class="card-text small text-muted">
                                <?= htmlentities(substr($xe['mo_ta'], 0, 100)) ?>...
                            </p>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <?php if (!empty($xe['mau_sac'])): ?>
                                <span class="badge bg-secondary me-1">
                                    <i class="fas fa-palette"></i> <?= htmlentities($xe['mau_sac']) ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($xe['nam_san_xuat'])): ?>
                                <span class="badge bg-info me-1">
                                    <i class="fas fa-calendar"></i> <?= $xe['nam_san_xuat'] ?>
                                </span>
                            <?php endif; ?>
                            <span class="badge bg-success">
                                <i class="fas fa-users"></i> <?= $xe['so_cho_ngoi'] ?> chỗ
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="text-primary fs-5">
                                    <?= format_money($xe['gia_thue_theo_ngay']) ?>
                                </strong>
                                <small class="text-muted">/ngày</small>
                            </div>
                            
                            <?php if (isset($_SESSION['customer_id'])): ?>
                                <a href="dat_xe.php?id=<?= $xe['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-calendar-plus"></i> Đặt xe
                                </a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-outline-primary">
                                    <i class="fas fa-sign-in-alt"></i> Đăng nhập để đặt
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include("footer.php"); ?>