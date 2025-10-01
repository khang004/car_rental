<?php
include("../admin_common.php");
check_admin_login();

$id = intval($_GET['id'] ?? 0);
$error = "";
$success = "";

// Lấy thông tin loại xe
$sql = "SELECT * FROM loai_xe WHERE id = ?";
$loai_xe = db_select($sql, [$id]);

if (empty($loai_xe)) {
    js_alert("Loại xe không tồn tại!");
    redirect_to("admin/loai_xe/");
}

$loai_xe = $loai_xe[0];

if (is_post_method()) {
    $ten_loai = trim($_POST['ten_loai'] ?? '');
    $mo_ta = trim($_POST['mo_ta'] ?? '');
    $trang_thai = intval($_POST['trang_thai'] ?? 1);
    
    // Validation
    if (empty($ten_loai)) {
        $error = "Vui lòng nhập tên loại xe!";
    } else {
        // Kiểm tra trùng tên (trừ bản ghi hiện tại)
        $sql_check = "SELECT COUNT(*) as count FROM loai_xe WHERE ten_loai = ? AND id != ?";
        $check_result = db_select($sql_check, [$ten_loai, $id]);
        
        if ($check_result[0]['count'] > 0) {
            $error = "Tên loại xe đã tồn tại!";
        } else {
            // Cập nhật
            $sql = "UPDATE loai_xe SET ten_loai = ?, mo_ta = ?, trang_thai = ? WHERE id = ?";
            $result = db_execute($sql, [$ten_loai, $mo_ta, $trang_thai, $id]);
            
            if ($result) {
                $success = "Cập nhật loại xe thành công!";
                // Cập nhật lại dữ liệu hiển thị
                $loai_xe['ten_loai'] = $ten_loai;
                $loai_xe['mo_ta'] = $mo_ta;
                $loai_xe['trang_thai'] = $trang_thai;
            } else {
                $error = "Có lỗi xảy ra, vui lòng thử lại!";
            }
        }
    }
}

include("../header.php");
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="fas fa-edit"></i> Sửa loại xe</h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Tên loại xe <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="ten_loai" 
                               value="<?= htmlentities($loai_xe['ten_loai']) ?>" 
                               placeholder="Ví dụ: Xe máy, Ô tô 4 chỗ..." required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="mo_ta" rows="4" 
                                  placeholder="Mô tả chi tiết về loại xe..."><?= htmlentities($loai_xe['mo_ta']) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="trang_thai">
                            <option value="1" <?= $loai_xe['trang_thai'] == 1 ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="0" <?= $loai_xe['trang_thai'] == 0 ? 'selected' : '' ?>>Ngừng hoạt động</option>
                        </select>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include("../footer.php"); ?>