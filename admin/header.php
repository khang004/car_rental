<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị XeDeep</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('css/admin.css') ?>" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= ROOT_PATH ?>/admin/">
                <i class="fas fa-car"></i> XeDeep Admin
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ROOT_PATH ?>/admin/">
                            <i class="fas fa-dashboard"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-car"></i> Quản lý xe
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= ROOT_PATH ?>/admin/loai_xe/">Loại xe</a></li>
                            <li><a class="dropdown-item" href="<?= ROOT_PATH ?>/admin/xe/">Danh sách xe</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ROOT_PATH ?>/admin/don_thue_xe/">
                            <i class="fas fa-file-contract"></i> Đơn thuê xe
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ROOT_PATH ?>/admin/khach_hang/">
                            <i class="fas fa-users"></i> Khách hàng
                        </a>
                    </li>
                    <?php if(isset($_SESSION['admin_role']) && $_SESSION['admin_role'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ROOT_PATH ?>/admin/quan_tri_vien/">
                            <i class="fas fa-user-shield"></i> Quản trị viên
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ROOT_PATH ?>/admin/danh_gia/">
                            <i class="fas fa-star"></i> Đánh giá
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ROOT_PATH ?>/admin/bao_tri_xe/">
                            <i class="fas fa-tools"></i> Bảo trì xe
                        </a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> 
                            <?= isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin' ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= ROOT_PATH ?>/admin/profile.php">Thông tin cá nhân</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= ROOT_PATH ?>/admin/logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4"><?php $current_admin = get_current_admin(); ?>