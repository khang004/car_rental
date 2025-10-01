<?php
include(__DIR__ . "/../admin_common.php");

// Kiểm tra đăng nhập admin
check_admin_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "<div class='alert alert-danger'>ID đánh giá không hợp lệ</div>";
    exit;
}

// Truy vấn thông tin đánh giá chi tiết - đã loại bỏ cột gia_thue không tồn tại
$sql = "SELECT dg.*, 
               kh.ho_ten, kh.email, kh.so_dien_thoai, kh.dia_chi,
               CONCAT('DH', LPAD(dt.id, 6, '0')) as ma_don,
               dt.ngay_bat_dau, dt.ngay_ket_thuc, dt.tong_tien, dt.trang_thai,
               x.ten_xe, x.id as xe_id,
               DATE_FORMAT(dg.ngay_danh_gia, '%d/%m/%Y lúc %H:%i') as ngay_danh_gia_formatted
        FROM danh_gia dg
        INNER JOIN khach_hang kh ON dg.khach_hang_id = kh.id
        INNER JOIN don_thue_xe dt ON dg.don_thue_xe_id = dt.id
        INNER JOIN xe x ON dt.xe_id = x.id
        WHERE dg.id = ?";

$review = db_select($sql, [$id]);

if (empty($review)) {
    echo "<div class='alert alert-danger'>Không tìm thấy đánh giá</div>";
    exit;
}

$review = $review[0];
?>

<div class="row">
    <div class="col-md-6">
        <!-- Thông tin khách hàng -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-user"></i> Thông tin khách hàng</h6>
            </div>
            <div class="card-body">
                <p><strong>Họ tên:</strong> <?= htmlspecialchars($review['ho_ten']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($review['email']) ?></p>
                <p><strong>Điện thoại:</strong> <?= htmlspecialchars($review['so_dien_thoai'] ?? 'Chưa có') ?></p>
                <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($review['dia_chi'] ?? 'Chưa có') ?></p>
            </div>
        </div>
        
        <!-- Thông tin đơn thuê -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-file-contract"></i> Thông tin đơn thuê</h6>
            </div>
            <div class="card-body">
                <p><strong>Mã đơn:</strong> <span class="badge bg-info"><?= $review['ma_don'] ?></span></p>
                <p><strong>Thời gian thuê:</strong> 
                    <?= date('d/m/Y H:i', strtotime($review['ngay_bat_dau'])) ?> - 
                    <?= date('d/m/Y H:i', strtotime($review['ngay_ket_thuc'])) ?>
                </p>
                <p><strong>Tổng tiền:</strong> <?= number_format($review['tong_tien']) ?> VNĐ</p>
                <p><strong>Trạng thái:</strong> 
                    <span class="badge <?= $review['trang_thai'] == 'hoan_thanh' ? 'bg-success' : 'bg-warning' ?>">
                        <?= ucfirst($review['trang_thai']) ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <!-- Thông tin xe -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-car"></i> Thông tin xe</h6>
            </div>
            <div class="card-body">
                <p><strong>Tên xe:</strong> <?= htmlspecialchars($review['ten_xe']) ?></p>
                <p><strong>Mã xe:</strong> <?= $review['xe_id'] ?></p>
            </div>
        </div>
        
        <!-- Đánh giá -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-star"></i> Chi tiết đánh giá</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Điểm đánh giá:</strong>
                    <div class="rating-stars mt-1">
                        <?php 
                        $rating = (int)$review['diem_so'];
                        for ($i = 1; $i <= 5; $i++): 
                        ?>
                            <i class="fas fa-star <?= $i <= $rating ? 'text-warning' : 'text-muted' ?>" style="font-size: 1.2em;"></i>
                        <?php endfor; ?>
                        <span class="ms-2 fw-bold"><?= $rating ?>/5</span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Nội dung đánh giá:</strong>
                    <div class="border rounded p-3 mt-2 bg-light">
                        <?= nl2br(htmlspecialchars($review['noi_dung'])) ?>
                    </div>
                </div>
                
                <div class="mb-3">
                    <strong>Ngày đánh giá:</strong>
                    <span class="text-muted"><?= $review['ngay_danh_gia_formatted'] ?></span>
                </div>
                
                <?php if (!empty($review['phan_hoi'])): ?>
                    <div class="mb-3">
                        <strong>Phản hồi từ quản trị:</strong>
                        <div class="border rounded p-3 mt-2 bg-success bg-opacity-10">
                            <i class="fas fa-reply me-2 text-success"></i>
                            <?= nl2br(htmlspecialchars($review['phan_hoi'])) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Chưa có phản hồi cho đánh giá này
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>