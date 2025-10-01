<?php
// filepath: d:\xampp\htdocs\xe\admin\khach_hang\index.php
include("../admin_common.php");
check_admin_login();

// Lấy tham số tìm kiếm
$search = trim($_GET['search'] ?? '');
$trang_thai = $_GET['trang_thai'] ?? '';

// Xây dựng câu truy vấn
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(kh.ho_ten LIKE ? OR kh.email LIKE ? OR kh.so_dien_thoai LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($trang_thai !== '') {
    $where_conditions[] = "kh.trang_thai = ?"; // Chỉ rõ bảng khach_hang
    $params[] = intval($trang_thai);
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Lấy danh sách khách hàng với thông tin thống kê
$sql = "
    SELECT kh.*, 
           COUNT(dt.id) as so_don_thue,
           COALESCE(SUM(CASE WHEN dt.trang_thai = 'da_tra_xe' THEN dt.tong_tien ELSE 0 END), 0) as tong_chi_tieu
    FROM khach_hang kh
    LEFT JOIN don_thue_xe dt ON kh.id = dt.khach_hang_id
    $where_clause
    GROUP BY kh.id
    ORDER BY kh.ngay_dang_ky DESC
";

try {
    $khach_hang_list = db_select($sql, $params);
} catch (Exception $e) {
    // Nếu có lỗi, lấy danh sách khách hàng đơn giản
    $sql_simple = "SELECT * FROM khach_hang kh $where_clause ORDER BY kh.ngay_dang_ky DESC";
    $khach_hang_list = db_select($sql_simple, $params);
    // Thêm các trường mặc định cho thống kê
    foreach ($khach_hang_list as &$kh) {
        $kh['so_don_thue'] = 0;
        $kh['tong_chi_tieu'] = 0;
    }
}

include("../header.php");
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="fas fa-users"></i> Quản lý khách hàng</h2>
        </div>
    </div>
</div>

<!-- Bộ lọc tìm kiếm -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" class="form-control" name="search" 
                               value="<?= htmlentities($search) ?>" 
                               placeholder="Tên, email, số điện thoại...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="trang_thai">
                            <option value="">Tất cả trạng thái</option>
                            <option value="1" <?= $trang_thai === '1' ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="0" <?= $trang_thai === '0' ? 'selected' : '' ?>>Bị khóa</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
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

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Avatar</th>
                                <th>Thông tin khách hàng</th>
                                <th>Liên hệ</th>
                                <th>Thống kê</th>
                                <th>Trạng thái</th>
                                <th>Ngày đăng ký</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($khach_hang_list)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Không tìm thấy khách hàng nào</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($khach_hang_list as $kh): ?>
                                    <tr>
                                        <td class="text-center">
                                            <?php if (!empty($kh['avatar'])): ?>
                                                <img src="<?= upload($kh['avatar']) ?>" 
                                                     class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light text-center d-inline-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px; border-radius: 50%;">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlentities($kh['ho_ten']) ?></strong>
                                            <?php if (!empty($kh['ngay_sinh'])): ?>
                                                <br><small class="text-muted">Sinh: <?= format_date($kh['ngay_sinh']) ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($kh['so_giay_phep_lai_xe'])): ?>
                                                <br><small class="text-muted">GPLX: <?= htmlentities($kh['so_giay_phep_lai_xe']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div><i class="fas fa-envelope text-muted"></i> <?= htmlentities($kh['email']) ?></div>
                                            <?php if (!empty($kh['so_dien_thoai'])): ?>
                                                <div><i class="fas fa-phone text-muted"></i> <?= htmlentities($kh['so_dien_thoai']) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($kh['dia_chi'])): ?>
                                                <div><i class="fas fa-map-marker-alt text-muted"></i> 
                                                     <small><?= htmlentities(substr($kh['dia_chi'], 0, 30)) ?>...</small></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small>
                                                <div><strong>Số đơn:</strong> <?= number_format($kh['so_don_thue']) ?></div>
                                                <div><strong>Chi tiêu:</strong> <?= format_money($kh['tong_chi_tieu'] ?? 0) ?></div>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if ($kh['trang_thai'] == 1): ?>
                                                <span class="badge bg-success"><i class="fas fa-check"></i> Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><i class="fas fa-lock"></i> Bị khóa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?= format_datetime($kh['ngay_dang_ky']) ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="edit.php?id=<?= $kh['id'] ?>" class="btn btn-warning" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($kh['trang_thai'] == 1): ?>
                                                    <a href="toggle_status.php?id=<?= $kh['id'] ?>&status=0" 
                                                       class="btn btn-danger" title="Khóa tài khoản"
                                                       onclick="return confirm('Bạn có chắc chắn muốn khóa tài khoản này?')">
                                                        <i class="fas fa-lock"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="toggle_status.php?id=<?= $kh['id'] ?>&status=1" 
                                                       class="btn btn-success" title="Mở khóa tài khoản"
                                                       onclick="return confirm('Bạn có chắc chắn muốn mở khóa tài khoản này?')">
                                                        <i class="fas fa-unlock"></i>
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
            </div>
        </div>
    </div>
</div>

<?php include("../footer.php"); ?>