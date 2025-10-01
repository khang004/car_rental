<?php
include("customer_common.php");

// Xử lý đăng nhập
if (is_post_method()) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ email và mật khẩu!";
    } else {
        $sql = "SELECT * FROM khach_hang WHERE email = ? AND trang_thai = 1";
        $customer = db_select($sql, [$email]);
        
        if (!empty($customer) && password_verify($password, $customer[0]['mat_khau'])) {
            $_SESSION['customer_id'] = $customer[0]['id'];
            $_SESSION['customer_name'] = $customer[0]['ho_ten'];
            $_SESSION['customer_email'] = $customer[0]['email'];
            
            redirect_to('khach_hang/index.php');
        } else {
            $error = "Email hoặc mật khẩu không chính xác!";
        }
    }
}

include("header.php");
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0"><i class="fas fa-sign-in-alt"></i> Đăng nhập</h4>
            </div>
            
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" class="form-control" name="email" 
                               value="<?= htmlentities($email ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-lock"></i> Mật khẩu
                        </label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập
                    </button>
                    
                    <div class="text-center">
                        <p class="mb-0">Chưa có tài khoản? 
                            <a href="register.php" class="text-decoration-none">Đăng ký ngay</a>
                        </p>
                    </div>
                </form>
                
                <hr>
                
                <div class="text-muted small">
                    <h6>Tài khoản demo:</h6>
                    <p class="mb-1">Email: khach1@email.com</p>
                    <p class="mb-0">Mật khẩu: password</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>