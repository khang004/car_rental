<?php
include("../admin_common.php");
check_admin_login();

// Lấy danh sách loại xe với kiểm tra cột tồn tại
$sql = "SELECT * FROM loai_xe ORDER BY ngay_tao DESC";
$loai_xe_list = db_select($sql);

include("../header.php");
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="fas fa-list"></i> Quản lý loại xe</h2>
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm loại xe
            </a>
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
                                <th>ID</th>
                                <th>Tên loại xe</th>
                                <th>Mô tả</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($loai_xe_list)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Chưa có loại xe nào</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($loai_xe_list as $item): ?>
                                    <tr>
                                        <td><?= $item['id'] ?? 'N/A' ?></td>
                                        <td><strong><?= htmlentities($item['ten_loai'] ?? 'Chưa có tên') ?></strong></td>
                                        <td><?= htmlentities($item['mo_ta'] ?? 'Chưa có mô tả') ?></td>
                                        <td>
                                            <?php 
                                            // Kiểm tra an toàn cho cột trang_thai
                                            $trang_thai = $item['trang_thai'] ?? 1; // Mặc định là hoạt động
                                            if ($trang_thai == 1): 
                                            ?>
                                                <span class="badge bg-success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Ngừng hoạt động</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            // Kiểm tra và format ngày tạo
                                            if (isset($item['ngay_tao']) && !empty($item['ngay_tao'])) {
                                                echo format_datetime($item['ngay_tao']);
                                            } else {
                                                echo 'Chưa có thông tin';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="edit.php?id=<?= $item['id'] ?? 0 ?>" 
                                                   class="btn btn-sm btn-warning" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete.php?id=<?= $item['id'] ?? 0 ?>" 
                                                   class="btn btn-sm btn-danger" title="Xóa"
                                                   onclick="return confirmDelete('Bạn có chắc chắn muốn xóa loại xe này?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (empty($loai_xe_list)): ?>
                    <div class="text-center mt-4">
                        <i class="fas fa-car fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có loại xe nào</h5>
                        <p class="text-muted">Hãy thêm loại xe đầu tiên để bắt đầu quản lý.</p>
                        <a href="create.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm loại xe đầu tiên
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Script xác nhận xóa -->
<script>
function confirmDelete(message) {
    return confirm(message || 'Bạn có chắc chắn muốn xóa?');
}
</script>

<?php include("../footer.php"); ?>