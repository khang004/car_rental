<?php
include("customer_common.php");

$error = "";
$success = "";

// Xử lý đăng ký
if (is_post_method()) {
    $ho_ten = trim($_POST['ho_ten'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
    $dia_chi = trim($_POST['dia_chi'] ?? '');
    $ngay_sinh = trim($_POST['ngay_sinh'] ?? '');
    $so_giay_phep_lai_xe = trim($_POST['so_giay_phep_lai_xe'] ?? '');
    
    // Validation
    if (empty($ho_ten)) {
        $error = "Vui lòng nhập họ tên!";
    } elseif (empty($email)) {
        $error = "Vui lòng nhập email!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ!";
    } elseif (empty($password)) {
        $error = "Vui lòng nhập mật khẩu!";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự!";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp!";
    } else {
        // Kiểm tra email đã tồn tại
        $sql_check = "SELECT COUNT(*) as count FROM khach_hang WHERE email = ?";
        $check_result = db_select($sql_check, [$email]);
        
        if ($check_result[0]['count'] > 0) {
            $error = "Email đã được đăng ký!";
        } else {
            // Mã hóa mật khẩu
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Thêm khách hàng mới
            $sql = "INSERT INTO khach_hang (ho_ten, email, mat_khau, so_dien_thoai, dia_chi, ngay_sinh, so_giay_phep_lai_xe) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $result = db_execute($sql, [
                $ho_ten, $email, $password_hash, $so_dien_thoai, 
                $dia_chi, $ngay_sinh ?: null, $so_giay_phep_lai_xe
            ]);
            
            if ($result) {
                $success = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
                // Reset form
                $ho_ten = $email = $so_dien_thoai = $dia_chi = $ngay_sinh = $so_giay_phep_lai_xe = "";
            } else {
                $error = "Có lỗi xảy ra, vui lòng thử lại!";
            }
        }
    }
}

include("header.php");
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white text-center">
                <h4 class="mb-0"><i class="fas fa-user-plus"></i> Đăng ký tài khoản</h4>
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
                        <div class="mt-2">
                            <a href="login.php" class="btn btn-sm btn-primary">Đăng nhập ngay</a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="ho_ten" 
                                       value="<?= htmlentities($ho_ten ?? '') ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?= htmlentities($email ?? '') ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại</label>
                                <input type="tel" class="form-control" name="so_dien_thoai" 
                                       value="<?= htmlentities($so_dien_thoai ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ngày sinh</label>
                                <input type="date" class="form-control" name="ngay_sinh" 
                                       value="<?= $ngay_sinh ?? '' ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Địa chỉ</label>
                        <textarea class="form-control" name="dia_chi" rows="2" 
                                  placeholder="Nhập địa chỉ của bạn..."><?= htmlentities($dia_chi ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Số giấy phép lái xe</label>
                        <input type="text" class="form-control" name="so_giay_phep_lai_xe" 
                               value="<?= htmlentities($so_giay_phep_lai_xe ?? '') ?>"
                               placeholder="Ví dụ: B123456789">
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100 mb-3">
                        <i class="fas fa-user-plus"></i> Đăng ký
                    </button>
                    
                    <div class="text-center">
                        <p class="mb-0">Đã có tài khoản? 
                            <a href="login.php" class="text-decoration-none">Đăng nhập ngay</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>