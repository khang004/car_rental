<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XeDeep - Thuê xe nhanh chóng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="<?= ROOT_PATH ?>/khach_hang/">
                <i class="fas fa-car text-primary"></i> 
                <strong>XeDeep</strong>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ROOT_PATH ?>/khach_hang/">
                            <i class="fas fa-home"></i> Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ROOT_PATH ?>/khach_hang/xe.php">
                            <i class="fas fa-car"></i> Danh sách xe
                        </a>
                    </li>
                    <?php if (isset($_SESSION['customer_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ROOT_PATH ?>/khach_hang/don_thue.php">
                            <i class="fas fa-file-contract"></i> Đơn thuê của tôi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ROOT_PATH ?>/khach_hang/danh_gia.php">
                            <i class="fas fa-star"></i> Đánh giá dịch vụ
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['customer_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> 
                                <?= isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : 'Khách hàng' ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= ROOT_PATH ?>/khach_hang/profile.php">
                                    <i class="fas fa-user-edit"></i> Thông tin cá nhân
                                </a></li>
                                <li><a class="dropdown-item" href="<?= ROOT_PATH ?>/khach_hang/don_thue.php">
                                    <i class="fas fa-list"></i> Lịch sử thuê xe
                                </a></li>
                                <li><a class="dropdown-item" href="<?= ROOT_PATH ?>/khach_hang/danh_gia.php">
                                    <i class="fas fa-star"></i> Đánh giá dịch vụ
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= ROOT_PATH ?>/khach_hang/logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= ROOT_PATH ?>/khach_hang/login.php">
                                <i class="fas fa-sign-in-alt"></i> Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= ROOT_PATH ?>/khach_hang/register.php">
                                <i class="fas fa-user-plus"></i> Đăng ký
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4"><?php $current_customer = get_current_customer(); ?>