<?php
include("../admin_common.php");
check_admin_login();

$error = "";
$success = "";

// Lấy ID xe cần sửa
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    js_alert("ID xe không hợp lệ!");
    redirect_to("admin/xe/index.php");
}

// Lấy thông tin xe hiện tại
$sql_xe = "SELECT x.*, lx.ten_loai 
           FROM xe x 
           LEFT JOIN loai_xe lx ON x.loai_xe_id = lx.id 
           WHERE x.id = ?";
$xe_info = db_select($sql_xe, [$id]);

if (empty($xe_info)) {
    js_alert("Không tìm thấy xe!");
    redirect_to("admin/xe/index.php");
}

$xe = $xe_info[0];

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
        // Kiểm tra trùng biển số (trừ xe hiện tại)
        $sql_check = "SELECT COUNT(*) as count FROM xe WHERE bien_so_xe = ? AND id != ?";
        $check_result = db_select($sql_check, [$bien_so_xe, $id]);

        if ($check_result[0]['count'] > 0) {
            $error = "Biển số xe đã tồn tại!";
        } else {
            // Upload hình ảnh mới (nếu có)
            $hinh_anh = $xe['hinh_anh']; // Giữ hình cũ mặc định
            if (!empty($_FILES['hinh_anh']['name'])) {
                $new_image = upload_and_return_filename('hinh_anh', 'xe');
                if ($new_image) {
                    // Xóa hình cũ nếu có
                    if (!empty($xe['hinh_anh']) && file_exists(UPLOAD_PATH . '/' . $xe['hinh_anh'])) {
                        unlink(UPLOAD_PATH . '/' . $xe['hinh_anh']);
                    }
                    $hinh_anh = $new_image;
                }
            }

            // Cập nhật thông tin xe
            $sql = "UPDATE xe SET 
                        loai_xe_id = ?, bien_so_xe = ?, ten_xe = ?, hang_xe = ?, mau_sac = ?, 
                        nam_san_xuat = ?, gia_thue_theo_ngay = ?, gia_thue_gio = ?, 
                        hinh_anh = ?, mo_ta = ?, trang_thai = ?, so_km_hien_tai = ?,
                        ngay_cap_nhat = NOW()
                    WHERE id = ?";

            $result = db_execute($sql, [
                $loai_xe_id, $bien_so_xe, $ten_xe, $hang_xe, $mau_sac, $nam_san_xuat,
                $gia_thue_theo_ngay, $gia_thue_gio, $hinh_anh, $mo_ta, $trang_thai, 
                $so_km_hien_tai, $id
            ]);

            if ($result) {
                $success = "Cập nhật xe thành công!";
                // Refresh thông tin xe
                $xe_info = db_select($sql_xe, [$id]);
                $xe = $xe_info[0];
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
            <h2><i class="fas fa-edit"></i> Sửa xe: <?= htmlentities($xe['ten_xe']) ?></h2>
            <div>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <a href="index.php?id=<?= $id ?>" class="btn btn-info">
                    <i class="fas fa-eye"></i> Xem chi tiết
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Thông tin xe</h5>
            </div>
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
                                    <option value="<?= $loai['id'] ?>" <?= $xe['loai_xe_id'] == $loai['id'] ? 'selected' : '' ?>>
                                        <?= htmlentities($loai['ten_loai']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Biển số xe <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="bien_so_xe"
                                   value="<?= htmlentities($xe['bien_so_xe']) ?>"
                                   placeholder="29A1-12345" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tên xe <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ten_xe"
                                   value="<?= htmlentities($xe['ten_xe']) ?>"
                                   placeholder="Honda Wave Alpha" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hãng xe</label>
                            <input type="text" class="form-control" name="hang_xe"
                                   value="<?= htmlentities($xe['hang_xe'] ?? '') ?>"
                                   placeholder="Honda, Toyota, Yamaha...">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Màu sắc</label>
                            <input type="text" class="form-control" name="mau_sac"
                                   value="<?= htmlentities($xe['mau_sac'] ?? '') ?>"
                                   placeholder="Đỏ, Xanh, Trắng...">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Năm sản xuất</label>
                            <input type="number" class="form-control" name="nam_san_xuat"
                                   value="<?= $xe['nam_san_xuat'] ?? '' ?>"
                                   min="1990" max="<?= date('Y') ?>" placeholder="<?= date('Y') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Số km hiện tại</label>
                            <input type="number" class="form-control" name="so_km_hien_tai"
                                   value="<?= $xe['so_km_hien_tai'] ?? 0 ?>" min="0" placeholder="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Giá thuê/ngày <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="gia_thue_theo_ngay"
                                   value="<?= $xe['gia_thue_theo_ngay'] ?>"
                                   min="0" step="1000" placeholder="150000" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Giá thuê/giờ</label>
                            <input type="number" class="form-control" name="gia_thue_gio"
                                   value="<?= $xe['gia_thue_gio'] ?? '' ?>"
                                   min="0" step="1000" placeholder="20000">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select class="form-select" name="trang_thai">
                                <?= get_xe_status_options($xe['trang_thai']) ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hình ảnh xe</label>
                        <?php if (!empty($xe['hinh_anh'])): ?>
                            <div class="mb-2">
                                <img src="<?= asset('upload/' . $xe['hinh_anh']) ?>" 
                                     alt="Hình ảnh xe" class="img-thumbnail" style="max-width: 200px;">
                                <br><small class="text-muted">Hình ảnh hiện tại</small>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="hinh_anh" accept="image/*">
                        <small class="text-muted">Chỉ chấp nhận file ảnh (JPG, PNG, GIF). Để trống nếu không muốn thay đổi.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea class="form-control" name="mo_ta" rows="4"
                                  placeholder="Mô tả chi tiết về xe..."><?= htmlentities($xe['mo_ta'] ?? '') ?></textarea>
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
    
    <div class="col-md-4">
        <!-- Thông tin bổ sung -->
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin bổ sung</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>ID xe:</strong> <?= $xe['id'] ?>
                </div>
                <div class="mb-3">
                    <strong>Loại xe hiện tại:</strong><br>
                    <span class="badge bg-info"><?= htmlentities($xe['ten_loai'] ?? 'Chưa xác định') ?></span>
                </div>
                <div class="mb-3">
                    <strong>Ngày tạo:</strong><br>
                    <?= isset($xe['ngay_tao']) ? format_datetime($xe['ngay_tao']) : 'Chưa có thông tin' ?>
                </div>
                <div class="mb-3">
                    <strong>Cập nhật lần cuối:</strong><br>
                    <?= isset($xe['ngay_cap_nhat']) ? format_datetime($xe['ngay_cap_nhat']) : 'Chưa có thông tin' ?>
                </div>
                <div class="mb-3">
                    <strong>Trạng thái hiện tại:</strong><br>
                    <?php
                    $status_classes = [
                        'san_sang' => 'success',
                        'dang_thue' => 'primary', 
                        'bao_tri' => 'warning',
                        'ngung_hoat_dong' => 'danger'
                    ];
                    $status_names = [
                        'san_sang' => 'Sẵn sàng',
                        'dang_thue' => 'Đang thuê',
                        'bao_tri' => 'Bảo trì', 
                        'ngung_hoat_dong' => 'Ngừng hoạt động'
                    ];
                    $class = $status_classes[$xe['trang_thai']] ?? 'secondary';
                    $name = $status_names[$xe['trang_thai']] ?? $xe['trang_thai'];
                    ?>
                    <span class="badge bg-<?= $class ?>"><?= $name ?></span>
                </div>
            </div>
        </div>

        <!-- Lịch sử thuê xe -->
        <div class="card shadow mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Lịch sử thuê</h5>
            </div>
            <div class="card-body">
                <?php
                $sql_history = "SELECT COUNT(*) as total_orders, 
                                      SUM(CASE WHEN trang_thai = 'da_tra_xe' THEN 1 ELSE 0 END) as completed_orders
                                FROM don_thue_xe WHERE xe_id = ?";
                $history = db_select($sql_history, [$id]);
                $stats = $history[0];
                ?>
                <div class="mb-2">
                    <strong>Tổng số lần thuê:</strong> <?= $stats['total_orders'] ?>
                </div>
                <div class="mb-2">
                    <strong>Hoàn thành:</strong> <?= $stats['completed_orders'] ?>
                </div>
                <div class="mb-2">
                    <strong>Tỷ lệ hoàn thành:</strong> 
                    <?php
                    $rate = $stats['total_orders'] > 0 ? 
                           round(($stats['completed_orders'] / $stats['total_orders']) * 100, 1) : 0;
                    ?>
                    <?= $rate ?>%
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../footer.php"); ?>