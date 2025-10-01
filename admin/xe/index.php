<?php
include("../admin_common.php");
check_admin_login();

// Lấy tham số tìm kiếm
$search = trim($_GET['search'] ?? '');
$loai_xe_id = intval($_GET['loai_xe_id'] ?? 0);
$trang_thai = $_GET['trang_thai'] ?? '';

// Xây dựng câu truy vấn
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(x.bien_so LIKE ? OR x.ten_xe LIKE ? OR x.hang_xe LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($loai_xe_id > 0) {
    $where_conditions[] = "x.loai_xe_id = ?";
    $params[] = $loai_xe_id;
}

if (!empty($trang_thai)) {
    $where_conditions[] = "x.trang_thai = ?";
    $params[] = $trang_thai;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

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
$sql_loai_xe = "SELECT * FROM loai_xe WHERE trang_thai = 1 ORDER BY ten_loai";
$loai_xe_list = db_select($sql_loai_xe);

include("../header.php");
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="fas fa-car"></i> Quản lý xe</h2>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm xe
            </a>
        </div>
    </div>
</div>

<!-- Bộ lọc tìm kiếm -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" class="form-control" name="search" 
                               value="<?= htmlentities($search) ?>" 
                               placeholder="Biển số, tên xe, hãng xe...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Loại xe</label>
                        <select class="form-select" name="loai_xe_id">
                            <option value="">Tất cả loại xe</option>
                            <?php foreach ($loai_xe_list as $loai): ?>
                                <option value="<?= $loai['id'] ?>" <?= $loai_xe_id == $loai['id'] ? 'selected' : '' ?>>
                                    <?= htmlentities($loai['ten_loai']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="trang_thai">
                            <option value="">Tất cả trạng thái</option>
                            <option value="san_sang" <?= $trang_thai == 'san_sang' ? 'selected' : '' ?>>Sẵn sàng</option>
                            <option value="dang_thue" <?= $trang_thai == 'dang_thue' ? 'selected' : '' ?>>Đang thuê</option>
                            <option value="bao_tri" <?= $trang_thai == 'bao_tri' ? 'selected' : '' ?>>Bảo trì</option>
                            <option value="ngung_hoat_dong" <?= $trang_thai == 'ngung_hoat_dong' ? 'selected' : '' ?>>Ngừng hoạt động</option>
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
                                <th>Hình ảnh</th>
                                <th>Biển số</th>
                                <th>Tên xe</th>
                                <th>Loại xe</th>
                                <th>Giá thuê/ngày</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($xe_list)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Không tìm thấy xe nào</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($xe_list as $xe): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($xe['hinh_anh'])): ?>
                                                <img src="<?= upload($xe['hinh_anh']) ?>" 
                                                     class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light text-center" style="width: 60px; height: 60px; line-height: 60px;">
                                                    <i class="fas fa-car text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlentities($xe['bien_so_xe']) ?></strong></td>
                                        <td>
                                            <?= htmlentities($xe['ten_xe']) ?>
                                            <?php if (!empty($xe['hang_xe'])): ?>
                                                <br><small class="text-muted"><?= htmlentities($xe['hang_xe']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlentities($xe['ten_loai']) ?></td>
                                        <td><?= format_money($xe['gia_thue_theo_ngay']) ?></td>
                                        <td>
                                            <?php
                                            $status_class = [
                                                'san_sang' => 'success',
                                                'dang_thue' => 'primary',
                                                'bao_tri' => 'warning',
                                                'ngung_hoat_dong' => 'danger'
                                            ];
                                            $status_text = [
                                                'san_sang' => 'Sẵn sàng',
                                                'dang_thue' => 'Đang thuê',
                                                'bao_tri' => 'Bảo trì',
                                                'ngung_hoat_dong' => 'Ngừng hoạt động'
                                            ];
                                            ?>
                                            <span class="badge bg-<?= $status_class[$xe['trang_thai']] ?>">
                                                <?= $status_text[$xe['trang_thai']] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit.php?id=<?= $xe['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete.php?id=<?= $xe['id'] ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirmDelete('Bạn có chắc chắn muốn xóa xe này?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
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