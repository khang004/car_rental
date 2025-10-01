<?php
include("include/common.php");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XeDeep - Hệ thống thuê xe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row min-vh-100">
            <div class="col-12 d-flex align-items-center justify-content-center">
                <div class="text-center">
                    <div class="card shadow-lg">
                        <div class="card-body p-5">
                            <i class="fas fa-car fa-5x text-primary mb-4"></i>
                            <h1 class="display-4 mb-4">XeDeep</h1>
                            <p class="lead mb-4">Hệ thống quản lý thuê xe chuyên nghiệp</p>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <i class="fas fa-users fa-3x text-info mb-3"></i>
                                            <h5>Khách hàng</h5>
                                            <p class="text-muted">Thuê xe nhanh chóng, tiện lợi</p>
                                            <a href="khach_hang/" class="btn btn-info">
                                                <i class="fas fa-sign-in-alt"></i> Truy cập
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <i class="fas fa-cog fa-3x text-success mb-3"></i>
                                            <h5>Quản trị</h5>
                                            <p class="text-muted">Quản lý hệ thống toàn diện</p>
                                            <a href="admin/" class="btn btn-success">
                                                <i class="fas fa-shield-alt"></i> Đăng nhập
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h6 class="text-muted">Tài khoản demo:</h6>
                                <small class="text-muted">
                                    Admin: admin@xedeep.com / admin123<br>
                                    Nhân viên: nhanvien@xedeep.com / nhanvien123<br>
                                    Khách hàng: khach1@email.com / 123456
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html><?php   
    echo "Hello, World!";
?>