<?php
include("../admin_common.php");
check_admin_login();

$error = "";
$success = "";

// Lấy danh sách loại xe
$sql_loai_xe = "SELECT * FROM loai_xe WHERE trang_thai = 1 ORDER BY ten_loai";
$loai_xe_list = db_select($sql_loai_xe);

// Xử lý khi submit
if (is_post_method()) {
    $loai_xe_id         = intval($_POST['loai_xe_id'] ?? 0);
    $bien_so_xe         = trim($_POST['bien_so_xe'] ?? '');
    $ten_xe             = trim($_POST['ten_xe'] ?? '');
    $hang_xe            = trim($_POST['hang_xe'] ?? '');
    $mau_sac            = trim($_POST['mau_sac'] ?? '');
    $nam_san_xuat       = intval($_POST['nam_san_xuat'] ?? 0);
    $gia_thue_theo_ngay = floatval($_POST['gia_thue_theo_ngay'] ?? 0);
    $gia_thue_gio       = floatval($_POST['gia_thue_gio'] ?? 0);
    $mo_ta              = trim($_POST['mo_ta'] ?? '');
    $trang_thai         = $_POST['trang_thai'] ?? 'san_sang';
    $so_km_hien_tai     = intval($_POST['so_km_hien_tai'] ?? 0);

    // Validation
    if ($loai_xe_id <= 0) {
        $error = "Vui lòng chọn loại xe!";
    } elseif (empty($bien_so_xe)) {
        $error = "Vui lòng nhập biển số xe!";
    } elseif (empty($ten_xe)) {
        $error = "Vui lòng nhập tên xe!";
    } elseif ($gia_thue_theo_ngay <= 0) {
        $error = "Vui lòng nhập giá thuê ngày hợp lệ!";
    } else {
        // Kiểm tra trùng biển số
        $sql_check = "SELECT COUNT(*) as count FROM xe WHERE bien_so_xe = ?";
        $check_result = db_select($sql_check, [$bien_so_xe]);

        if ($check_result[0]['count'] > 0) {
            $error = "Biển số xe đã tồn tại!";
        } else {
            // Upload hình ảnh
            $hinh_anh = upload_and_return_filename('hinh_anh', 'xe');

            // Thêm mới xe
            $sql = "INSERT INTO xe 
                        (loai_xe_id, bien_so_xe, ten_xe, hang_xe, mau_sac, nam_san_xuat, 
                         gia_thue_theo_ngay, gia_thue_gio, hinh_anh, mo_ta, trang_thai, so_km_hien_tai) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $result = db_execute($sql, [
                $loai_xe_id, $bien_so_xe, $ten_xe, $hang_xe, $mau_sac, $nam_san_xuat,
                $gia_thue_theo_ngay, $gia_thue_gio, $hinh_anh, $mo_ta, $trang_thai, $so_km_hien_tai
            ]);

            if ($result) {
                $success = "Thêm xe thành công!";
                // Reset form
                $loai_xe_id = $nam_san_xuat = $gia_thue_theo_ngay = $gia_thue_gio = $so_km_hien_tai = 0;
                $bien_so_xe = $ten_xe = $hang_xe = $mau_sac = $mo_ta = "";
                $trang_thai = 'san_sang';
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
            <h2><i class="fas fa-plus"></i> Thêm xe</h2>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
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

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Loại xe <span class="text-danger">*</span></label>
                            <select class="form-select" name="loai_xe_id" required>
                                <option value="">-- Chọn loại xe --</option>
                                <?php foreach ($loai_xe_list as $loai): ?>
                                    <option value="<?= $loai['id'] ?>" <?= ($loai_xe_id ?? 0) == $loai['id'] ? 'selected' : '' ?>>
                                        <?= htmlentities($loai['ten_loai']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Biển số xe <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="bien_so_xe"
                                   value="<?= htmlentities($bien_so_xe ?? '') ?>"
                                   placeholder="29A1-12345" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên xe <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ten_xe"
                                   value="<?= htmlentities($ten_xe ?? '') ?>"
                                   placeholder="Honda Wave Alpha" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hãng xe</label>
                            <input type="text" class="form-control" name="hang_xe"
                                   value="<?= htmlentities($hang_xe ?? '') ?>"
                                   placeholder="Honda, Toyota, Yamaha...">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Màu sắc</label>
                            <input type="text" class="form-control" name="mau_sac"
                                   value="<?= htmlentities($mau_sac ?? '') ?>"
                                   placeholder="Đỏ, Xanh, Trắng...">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Năm sản xuất</label>
                            <input type="number" class="form-control" name="nam_san_xuat"
                                   value="<?= $nam_san_xuat ?? '' ?>"
                                   min="1990" max="<?= date('Y') ?>" placeholder="<?= date('Y') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Số km hiện tại</label>
                            <input type="number" class="form-control" name="so_km_hien_tai"
                                   value="<?= $so_km_hien_tai ?? 0 ?>" min="0" placeholder="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Giá thuê/ngày <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="gia_thue_theo_ngay"
                                   value="<?= $gia_thue_theo_ngay ?? '' ?>"
                                   min="0" step="1000" placeholder="150000" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Giá thuê/giờ</label>
                            <input type="number" class="form-control" name="gia_thue_gio"
                                   value="<?= $gia_thue_gio ?? '' ?>"
                                   min="0" step="1000" placeholder="20000">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" name="trang_thai">
                                <?= get_xe_status_options($trang_thai ?? 'san_sang') ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hình ảnh xe</label>
                        <input type="file" class="form-control" name="hinh_anh" accept="image/*">
                        <small class="text-muted">Chỉ chấp nhận file ảnh (JPG, PNG, GIF)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="mo_ta" rows="4"
                                  placeholder="Mô tả chi tiết về xe..."><?= htmlentities($mo_ta ?? '') ?></textarea>
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
