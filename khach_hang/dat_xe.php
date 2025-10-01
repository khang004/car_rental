<?php
include("customer_common.php");
check_customer_login();

$xe_id = intval($_GET['id'] ?? 0);
$error = "";
$success = "";

// Lấy thông tin xe
$sql_xe = "
    SELECT x.*, lx.ten_loai 
    FROM xe x
    JOIN loai_xe lx ON x.loai_xe_id = lx.id
    WHERE x.id = ? AND x.trang_thai = 'san_sang'
";
$xe_info = db_select($sql_xe, [$xe_id]);

if (empty($xe_info)) {
    js_alert("Xe không tồn tại hoặc không còn sẵn sàng!");
    redirect_to("khach_hang/xe.php");
}

$xe = $xe_info[0];
$customer = get_current_customer();

// Xử lý đặt xe
if (is_post_method()) {
    $ngay_bat_dau = trim($_POST['ngay_bat_dau'] ?? '');
    $ngay_ket_thuc = trim($_POST['ngay_ket_thuc'] ?? '');
    $dia_diem_nhan = trim($_POST['dia_diem_nhan'] ?? '');
    $dia_diem_tra = trim($_POST['dia_diem_tra'] ?? '');
    $ghi_chu = trim($_POST['ghi_chu'] ?? '');
    
    // Validation
    if (empty($ngay_bat_dau)) {
        $error = "Vui lòng chọn ngày bắt đầu thuê!";
    } elseif (empty($ngay_ket_thuc)) {
        $error = "Vui lòng chọn ngày kết thúc thuê!";
    } elseif ($ngay_bat_dau >= $ngay_ket_thuc) {
        $error = "Ngày kết thúc phải sau ngày bắt đầu!";
    } elseif ($ngay_bat_dau < date('Y-m-d')) {
        $error = "Ngày bắt đầu không thể trong quá khứ!";
    } elseif (empty($dia_diem_nhan)) {
        $error = "Vui lòng nhập địa điểm nhận xe!";
    } elseif (empty($dia_diem_tra)) {
        $error = "Vui lòng nhập địa điểm trả xe!";
    } else {
        // Kiểm tra xe có sẵn trong khoảng thời gian không
        if (!is_xe_available_for_customer($xe_id, $ngay_bat_dau, $ngay_ket_thuc)) {
            $error = "Xe đã được đặt trong khoảng thời gian này!";
        } else {
            // Tính toán giá thuê
            $tong_tien = calculate_rental_total($xe['gia_thue_theo_ngay'], $ngay_bat_dau, $ngay_ket_thuc);
            $tien_coc = calculate_deposit($tong_tien);
            $ma_don_hang = generate_order_code();
            
            // Kiểm tra lại khách hàng có tồn tại không
            $customer_check = get_current_customer();
            if (!$customer_check) {
                $error = "Phiên đăng nhập không hợp lệ. Vui lòng đăng nhập lại!";
            } else {
                // Thêm đơn thuê xe
                $sql_insert = "
                    INSERT INTO don_thue_xe (
                        ma_don_hang, khach_hang_id, xe_id, ngay_bat_dau, ngay_ket_thuc,
                        dia_diem_nhan, dia_diem_tra, tong_tien, tien_coc, ghi_chu, trang_thai
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'cho_xac_nhan')
                ";
                
                try {
                    $result = db_execute($sql_insert, [
                        $ma_don_hang, $_SESSION['customer_id'], $xe_id, $ngay_bat_dau, $ngay_ket_thuc,
                        $dia_diem_nhan, $dia_diem_tra, $tong_tien, $tien_coc, $ghi_chu
                    ]);
                    
                    if ($result) {
                        $success = "Đặt xe thành công! Mã đơn hàng: $ma_don_hang";
                        js_alert("Đặt xe thành công! Chúng tôi sẽ liên hệ với bạn để xác nhận.");
                        redirect_to("khach_hang/don_thue.php");
                    } else {
                        $error = "Có lỗi xảy ra, vui lòng thử lại!";
                    }
                } catch (Exception $e) {
                    $error = "Lỗi hệ thống: Không thể tạo đơn thuê xe. Vui lòng đăng nhập lại!";
                    error_log("Lỗi foreign key constraint: " . $e->getMessage());
                }
            }
        }
    }
}

include("header.php");
?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-calendar-plus"></i> Đặt xe thuê</h4>
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
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ngày bắt đầu thuê <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="ngay_bat_dau" 
                                       value="<?= $ngay_bat_dau ?? date('Y-m-d', strtotime('+1 day')) ?>" 
                                       min="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ngày kết thúc thuê <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="ngay_ket_thuc" 
                                       value="<?= $ngay_ket_thuc ?? date('Y-m-d', strtotime('+2 days')) ?>" 
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Địa điểm nhận xe <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="dia_diem_nhan" 
                               value="<?= htmlentities($dia_diem_nhan ?? $customer['dia_chi'] ?? '') ?>" 
                               placeholder="Nhập địa chỉ nhận xe..." required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Địa điểm trả xe <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="dia_diem_tra" 
                               value="<?= htmlentities($dia_diem_tra ?? $customer['dia_chi'] ?? '') ?>" 
                               placeholder="Nhập địa chỉ trả xe..." required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea class="form-control" name="ghi_chu" rows="3" 
                                  placeholder="Thông tin bổ sung..."><?= htmlentities($ghi_chu ?? '') ?></textarea>
                    </div>
                    
                    <div class="text-end">
                        <a href="xe.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Xác nhận đặt xe
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Thông tin xe -->
        <div class="card shadow mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-car"></i> Thông tin xe</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($xe['hinh_anh'])): ?>
                    <img src="<?= upload($xe['hinh_anh']) ?>" class="img-fluid rounded mb-3" 
                         alt="<?= htmlentities($xe['ten_xe']) ?>">
                <?php endif; ?>
                
                <h5><?= htmlentities($xe['ten_xe']) ?></h5>
                <p class="text-muted mb-2">
                    <i class="fas fa-tag"></i> <?= htmlentities($xe['ten_loai']) ?> - 
                    <strong><?= htmlentities($xe['bien_so_xe']) ?></strong>
                </p>
                
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
                
                <div class="text-center">
                    <h4 class="text-primary mb-0">
                        <?= format_money($xe['gia_thue_theo_ngay']) ?>
                    </h4>
                    <small class="text-muted">/ ngày</small>
                </div>
            </div>
        </div>
        
        <!-- Thông tin khách hàng -->
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user"></i> Thông tin của bạn</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Họ tên:</strong> <?= htmlentities($customer['ho_ten']) ?></p>
                <p class="mb-1"><strong>Email:</strong> <?= htmlentities($customer['email']) ?></p>
                <?php if (!empty($customer['so_dien_thoai'])): ?>
                    <p class="mb-1"><strong>SĐT:</strong> <?= htmlentities($customer['so_dien_thoai']) ?></p>
                <?php endif; ?>
                <small class="text-muted">
                    <a href="profile.php" class="text-decoration-none">Cập nhật thông tin</a>
                </small>
            </div>
        </div>
    </div>
</div>

<script>
// Tự động tính toán và hiển thị tổng tiền khi thay đổi ngày
document.addEventListener('DOMContentLoaded', function() {
    const ngayBatDau = document.querySelector('input[name="ngay_bat_dau"]');
    const ngayKetThuc = document.querySelector('input[name="ngay_ket_thuc"]');
    const giaThueNgay = <?= $xe['gia_thue_theo_ngay'] ?>;
    
    function calculateTotal() {
        if (ngayBatDau.value && ngayKetThuc.value) {
            const start = new Date(ngayBatDau.value);
            const end = new Date(ngayKetThuc.value);
            const timeDiff = end.getTime() - start.getTime();
            const soNgay = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            if (soNgay > 0) {
                const tongTien = giaThueNgay * soNgay;
                const tienCoc = Math.round(tongTien * 0.3);
                
                // Hiển thị thông tin tính tiền (có thể thêm div để hiển thị)
                console.log('Số ngày:', soNgay, 'Tổng tiền:', tongTien, 'Tiền cọc:', tienCoc);
            }
        }
    }
    
    ngayBatDau.addEventListener('change', calculateTotal);
    ngayKetThuc.addEventListener('change', calculateTotal);
});
</script>

<?php include("footer.php"); ?>