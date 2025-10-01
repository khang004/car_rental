<?php
include("admin_common.php");

// Xử lý đăng nhập
if (is_post_method()) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ email và mật khẩu!";
    } else {
        $sql = "SELECT * FROM quan_tri_vien WHERE email = ? AND trang_thai = 1";
        $admin = db_select($sql, [$email]);
        
        if (!empty($admin) && password_verify($password, $admin[0]['mat_khau'])) {
            $_SESSION['admin_id'] = $admin[0]['id'];
            $_SESSION['admin_name'] = $admin[0]['ho_ten'];
            $_SESSION['admin_email'] = $admin[0]['email'];
            $_SESSION['admin_role'] = $admin[0]['vai_tro'];
            
            redirect_to('admin/index.php');
        } else {
            $error = "Email hoặc mật khẩu không chính xác!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - XeDeep Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-card">
                    <div class="login-header">
                        <i class="fas fa-car fa-3x mb-3"></i>
                        <h3>XeDeep Admin</h3>
                        <p class="mb-0">Đăng nhập vào hệ thống quản trị</p>
                    </div>
                    
                    <div class="login-body">
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
                                       value="<?= $email ?? '' ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-lock"></i> Mật khẩu
                                </label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt"></i> Đăng nhập
                            </button>
                        </form>
                        
                        <div class="mt-3 text-muted small">
                            <p class="mb-1">Tài khoản demo:</p>
                            <p class="mb-0">Admin: admin@xedeep.com / admin123</p>
                            <p class="mb-0">Nhân viên: nhanvien@xedeep.com / nhanvien123</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>