<?php
// filepath: d:\xampp\htdocs\xe\admin\don_thue_xe\index.php
include("../admin_common.php");
check_admin_login();

// Lấy tham số tìm kiếm và lọc
$search = trim($_GET['search'] ?? '');
$trang_thai = $_GET['trang_thai'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

// Xây dựng điều kiện WHERE
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(dt.ma_don_hang LIKE ? OR kh.ho_ten LIKE ? OR kh.so_dien_thoai LIKE ? OR x.bien_so_xe LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($trang_thai)) {
    $where_conditions[] = "dt.trang_thai = ?";
    $params[] = $trang_thai;
}

if (!empty($from_date)) {
    $where_conditions[] = "dt.ngay_bat_dau >= ?";
    $params[] = $from_date;
}

if (!empty($to_date)) {
    $where_conditions[] = "dt.ngay_ket_thuc <= ?";
    $params[] = $to_date;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Truy vấn danh sách đơn thuê xe - sửa tên cột theo đúng database schema
$sql = "
    SELECT dt.*, kh.ho_ten as ten_khach_hang, kh.so_dien_thoai, 
           x.bien_so_xe, x.ten_xe, x.mau_sac,
           qtv.ho_ten as ten_nguoi_xac_nhan
    FROM don_thue_xe dt
    JOIN khach_hang kh ON dt.khach_hang_id = kh.id
    JOIN xe x ON dt.xe_id = x.id
    LEFT JOIN quan_tri_vien qtv ON dt.nguoi_xac_nhan_id = qtv.id
    $where_clause
    ORDER BY dt.ngay_dat DESC
";

try {
    $don_thue_list = db_select($sql, $params);
} catch (Exception $e) {
    // Xử lý lỗi và hiển thị thông báo
    $don_thue_list = [];
    $error_message = "Có lỗi xảy ra khi tải dữ liệu: " . $e->getMessage();
}

include("../header.php");
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="fas fa-clipboard-list"></i> Quản lý đơn thuê xe</h2>
        </div>
    </div>
</div>

<?php if (isset($error_message)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> <?= htmlentities($error_message) ?>
    </div>
<?php endif; ?>

<!-- Bộ lọc tìm kiếm -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" class="form-control" name="search" 
                               value="<?= htmlentities($search) ?>" 
                               placeholder="Mã đơn, tên KH, SĐT, biển số...">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="trang_thai">
                            <option value="">Tất cả trạng thái</option>
                            <option value="cho_xac_nhan" <?= $trang_thai === 'cho_xac_nhan' ? 'selected' : '' ?>>Chờ xác nhận</option>
                            <option value="da_xac_nhan" <?= $trang_thai === 'da_xac_nhan' ? 'selected' : '' ?>>Đã xác nhận</option>
                            <option value="dang_thue" <?= $trang_thai === 'dang_thue' ? 'selected' : '' ?>>Đang thuê</option>
                            <option value="da_tra_xe" <?= $trang_thai === 'da_tra_xe' ? 'selected' : '' ?>>Đã trả xe</option>
                            <option value="da_huy" <?= $trang_thai === 'da_huy' ? 'selected' : '' ?>>Đã hủy</option>
                            <option value="qua_han" <?= $trang_thai === 'qua_han' ? 'selected' : '' ?>>Quá hạn</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" class="form-control" name="from_date" 
                               value="<?= htmlentities($from_date) ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" class="form-control" name="to_date" 
                               value="<?= htmlentities($to_date) ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Tìm
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-refresh"></i>
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
    <div class="col-12">
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Thông tin xe</th>
                                <th>Thời gian thuê</th>
                                <th>Địa điểm</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Người xác nhận</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($don_thue_list)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">Không tìm thấy đơn thuê nào</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($don_thue_list as $don): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlentities($don['ma_don_hang']) ?></strong>
                                            <br><small class="text-muted"><?= format_datetime($don['ngay_dat']) ?></small>
                                        </td>
                                        
                                        <td>
                                            <strong><?= htmlentities($don['ten_khach_hang']) ?></strong>
                                            <?php if (!empty($don['so_dien_thoai'])): ?>
                                                <br><small><i class="fas fa-phone"></i> <?= htmlentities($don['so_dien_thoai']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td>
                                            <strong><?= htmlentities($don['bien_so_xe']) ?></strong>
                                            <br><small><?= htmlentities($don['ten_xe']) ?></small>
                                            <?php if (!empty($don['mau_sac'])): ?>
                                                <br><small class="text-muted"><?= htmlentities($don['mau_sac']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td>
                                            <small>
                                                <strong>Từ:</strong> <?= format_date($don['ngay_bat_dau']) ?><br>
                                                <strong>Đến:</strong> <?= format_date($don['ngay_ket_thuc']) ?>
                                            </small>
                                        </td>
                                        
                                        <td class="small">
                                            <?php if (!empty($don['dia_diem_nhan'])): ?>
                                                <strong>Nhận:</strong> <?= htmlentities(substr($don['dia_diem_nhan'], 0, 30)) ?>...<br>
                                            <?php endif; ?>
                                            <?php if (!empty($don['dia_diem_tra'])): ?>
                                                <strong>Trả:</strong> <?= htmlentities(substr($don['dia_diem_tra'], 0, 30)) ?>...
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td>
                                            <strong><?= format_money($don['tong_tien']) ?></strong>
                                            <?php if ($don['tien_coc'] > 0): ?>
                                                <br><small class="text-info">Cọc: <?= format_money($don['tien_coc']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td>
                                            <span class="badge bg-<?= get_order_status_class($don['trang_thai']) ?>">
                                                <?= get_order_status_text($don['trang_thai']) ?>
                                            </span>
                                        </td>
                                        
                                        <td>
                                            <?php if (!empty($don['ten_nguoi_xac_nhan'])): ?>
                                                <small><?= htmlentities($don['ten_nguoi_xac_nhan']) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">Chưa xác nhận</small>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="view.php?id=<?= $don['id'] ?>" class="btn btn-info" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <?php if ($don['trang_thai'] == 'cho_xac_nhan'): ?>
                                                    <a href="update_status.php?id=<?= $don['id'] ?>&status=da_xac_nhan" 
                                                       class="btn btn-success" title="Xác nhận"
                                                       onclick="return confirm('Xác nhận đơn thuê này?')">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <a href="update_status.php?id=<?= $don['id'] ?>&status=da_huy" 
                                                       class="btn btn-danger" title="Hủy đơn"
                                                       onclick="return confirm('Hủy đơn thuê này?')">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php elseif ($don['trang_thai'] == 'da_xac_nhan'): ?>
                                                    <a href="update_status.php?id=<?= $don['id'] ?>&status=dang_thue" 
                                                       class="btn btn-primary" title="Bắt đầu cho thuê"
                                                       onclick="return confirm('Bắt đầu cho thuê xe?')">
                                                        <i class="fas fa-play"></i>
                                                    </a>
                                                <?php elseif ($don['trang_thai'] == 'dang_thue'): ?>
                                                    <a href="update_status.php?id=<?= $don['id'] ?>&status=da_tra_xe" 
                                                       class="btn btn-warning" title=" Xác nhận trả xe"
                                                       onclick="return confirm('Xác nhận đã trả xe?')">
                                                        <i class="fas fa-flag-checkered"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Thống kê nhanh -->
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Tổng số đơn:</strong> <?= count($don_thue_list) ?> | 
                    <strong>Tổng doanh thu:</strong> 
                    <?php
                    $total_revenue = 0;
                    foreach ($don_thue_list as $don) {
                        if (in_array($don['trang_thai'], ['da_tra_xe', 'dang_thue'])) {
                            $total_revenue += $don['tong_tien'];
                        }
                    }
                    echo format_money($total_revenue);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../footer.php"); ?>