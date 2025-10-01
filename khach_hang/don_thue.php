<?php
include("customer_common.php");
check_customer_login();

// Lấy tham số tìm kiếm
$trang_thai = $_GET['trang_thai'] ?? '';

// Xây dựng câu truy vấn
$where_conditions = ["dt.khach_hang_id = ?"];
$params = [$_SESSION['customer_id']];

if (!empty($trang_thai)) {
    $where_conditions[] = "dt.trang_thai = ?";
    $params[] = $trang_thai;
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Lấy danh sách đơn thuê xe
$sql = "
    SELECT dt.*, x.ten_xe, x.bien_so_xe, x.hinh_anh, lx.ten_loai
    FROM don_thue_xe dt
    JOIN xe x ON dt.xe_id = x.id
    JOIN loai_xe lx ON x.loai_xe_id = lx.id
    $where_clause
    ORDER BY dt.ngay_dat DESC
";
$don_thue_list = db_select($sql, $params);

include("header.php");
?>

<div class="row">
    <div class="col-12">
        <h2><i class="fas fa-file-contract"></i> Đơn thuê xe của tôi</h2>
        <p class="text-muted">Quản lý và theo dõi các đơn thuê xe của bạn</p>
    </div>
</div>

<!-- Bộ lọc -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Trạng thái đơn</label>
                        <select class="form-select" name="trang_thai">
                            <option value="">Tất cả trạng thái</option>
                            <option value="cho_xac_nhan" <?= $trang_thai == 'cho_xac_nhan' ? 'selected' : '' ?>>Chờ xác nhận</option>
                            <option value="da_xac_nhan" <?= $trang_thai == 'da_xac_nhan' ? 'selected' : '' ?>>Đã xác nhận</option>
                            <option value="dang_thue" <?= $trang_thai == 'dang_thue' ? 'selected' : '' ?>>Đang thuê</option>
                            <option value="da_tra_xe" <?= $trang_thai == 'da_tra_xe' ? 'selected' : '' ?>>Đã trả xe</option>
                            <option value="da_huy" <?= $trang_thai == 'da_huy' ? 'selected' : '' ?>>Đã hủy</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Lọc
                            </button>
                            <a href="don_thue.php" class="btn btn-secondary">
                                <i class="fas fa-refresh"></i> Tất cả
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Danh sách đơn thuê -->
<div class="row">
    <?php if (empty($don_thue_list)): ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> Bạn chưa có đơn thuê xe nào.
                <div class="mt-2">
                    <a href="xe.php" class="btn btn-primary">
                        <i class="fas fa-car"></i> Thuê xe ngay
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($don_thue_list as $don): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($don['hinh_anh'])): ?>
                        <img src="<?= upload($don['hinh_anh']) ?>" class="card-img-top" 
                             style="height: 150px; object-fit: cover;" alt="<?= htmlentities($don['ten_xe']) ?>">
                    <?php else: ?>
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                            <i class="fas fa-car fa-2x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0"><?= htmlentities($don['ten_xe']) ?></h6>
                            <span class="badge bg-<?= get_order_status_class($don['trang_thai']) ?>">
                                <?= get_order_status_text($don['trang_thai']) ?>
                            </span>
                        </div>
                        
                        <p class="text-muted small mb-2">
                            <i class="fas fa-tag"></i> <?= htmlentities($don['ten_loai']) ?> - 
                            <strong><?= htmlentities($don['bien_so_xe']) ?></strong>
                        </p>
                        
                        <p class="small mb-2">
                            <i class="fas fa-barcode"></i> <strong>Mã đơn:</strong> <?= $don['ma_don_hang'] ?>
                        </p>
                        
                        <p class="small mb-2">
                            <i class="fas fa-calendar"></i> 
                            <strong>Thuê:</strong> <?= format_date($don['ngay_bat_dau']) ?> 
                            - <?= format_date($don['ngay_ket_thuc']) ?>
                        </p>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <small>Tổng tiền:</small>
                                <strong class="text-primary"><?= format_money($don['tong_tien']) ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small>Tiền cọc:</small>
                                <strong class="text-warning"><?= format_money($don['tien_coc']) ?></strong>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <a href="chi_tiet_don.php?id=<?= $don['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> Chi tiết
                            </a>
                            
                            <?php if ($don['trang_thai'] == 'cho_xac_nhan' && can_cancel_order($don['ngay_bat_dau'])): ?>
                                <a href="huy_don.php?id=<?= $don['id'] ?>" class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Bạn có chắc chắn muốn hủy đơn này?')">
                                    <i class="fas fa-times"></i> Hủy đơn
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($don['trang_thai'] == 'da_tra_xe'): ?>
                                <a href="danh_gia.php?id=<?= $don['id'] ?>" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-star"></i> Đánh giá
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> Đặt ngày <?= format_datetime($don['ngay_dat']) ?>
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include("footer.php"); ?>