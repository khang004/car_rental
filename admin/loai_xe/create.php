<?php
include("../admin_common.php");
check_admin_login();

$error = "";
$success = "";

if (is_post_method()) {
    $ten_loai = trim($_POST['ten_loai'] ?? '');
    $mo_ta = trim($_POST['mo_ta'] ?? '');
    $trang_thai = intval($_POST['trang_thai'] ?? 1);
    
    // Validation
    if (empty($ten_loai)) {
        $error = "Vui lòng nhập tên loại xe!";
    } else {
        // Kiểm tra trùng tên
        $sql_check = "SELECT COUNT(*) as count FROM loai_xe WHERE ten_loai = ?";
        $check_result = db_select($sql_check, [$ten_loai]);
        
        if ($check_result[0]['count'] > 0) {
            $error = "Tên loại xe đã tồn tại!";
        } else {
            // Thêm mới
            $sql = "INSERT INTO loai_xe (ten_loai, mo_ta, trang_thai) VALUES (?, ?, ?)";
            $result = db_execute($sql, [$ten_loai, $mo_ta, $trang_thai]);
            
            if ($result) {
                $success = "Thêm loại xe thành công!";
                // Reset form
                $ten_loai = $mo_ta = "";
                $trang_thai = 1;
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
            <h2><i class="fas fa-plus"></i> Thêm loại xe</h2>
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
                               value="<?= htmlentities($ten_loai ?? '') ?>" 
                               placeholder="Ví dụ: Xe máy, Ô tô 4 chỗ..." required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="mo_ta" rows="4" 
                                  placeholder="Mô tả chi tiết về loại xe..."><?= htmlentities($mo_ta ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="trang_thai">
                            <option value="1" <?= ($trang_thai ?? 1) == 1 ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="0" <?= ($trang_thai ?? 1) == 0 ? 'selected' : '' ?>>Ngừng hoạt động</option>
                        </select>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu
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