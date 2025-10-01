<?php
include('customer_common.php');
check_customer_login();

$current_customer = get_current_customer();
$page_title = "Đánh giá dịch vụ";

// Xử lý gửi đánh giá
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_rating'])) {
    $don_thue_xe_id = (int)$_POST['don_thue_xe_id'];
    $diem_so = (int)$_POST['diem_so'];
    $noi_dung = trim($_POST['noi_dung']);
    
    // Kiểm tra đơn thuê xe có thuộc về khách hàng này không và đã hoàn thành
    $check_order = "SELECT id, trang_thai FROM don_thue_xe 
                   WHERE id = ? AND khach_hang_id = ? AND trang_thai = 'da_tra_xe'";
    $order = db_select($check_order, [$don_thue_xe_id, $_SESSION['customer_id']]);
    
    if (empty($order)) {
        js_alert('Không tìm thấy đơn thuê xe hoặc đơn chưa hoàn thành!');
        redirect_to('danh_gia.php');
    } else {
        // Kiểm tra đã đánh giá chưa
        $check_rating = "SELECT id FROM danh_gia WHERE don_thue_xe_id = ? AND khach_hang_id = ?";
        $existing_rating = db_select($check_rating, [$don_thue_xe_id, $_SESSION['customer_id']]);
        
        if (!empty($existing_rating)) {
            js_alert('Bạn đã đánh giá đơn thuê xe này rồi!');
            redirect_to('danh_gia.php');
        } else {
            // Thêm đánh giá mới
            $insert_rating = "INSERT INTO danh_gia (don_thue_xe_id, khach_hang_id, diem_so, noi_dung, ngay_danh_gia) 
                            VALUES (?, ?, ?, ?, NOW())";
            $result = db_execute($insert_rating, [$don_thue_xe_id, $_SESSION['customer_id'], $diem_so, $noi_dung]);
            
            if ($result) {
                js_alert('Cảm ơn bạn đã đánh giá dịch vụ của chúng tôi!');
                redirect_to('danh_gia.php');
            } else {
                js_alert('Có lỗi xảy ra khi gửi đánh giá!');
            }
        }
    }
}

// Lấy danh sách đơn thuê đã hoàn thành
$orders_sql = "SELECT dt.id, dt.ngay_bat_dau, dt.ngay_ket_thuc, dt.tong_tien,
                      x.ten_xe, lx.ten_loai,
                      dg.id as da_danh_gia
               FROM don_thue_xe dt
               JOIN xe x ON dt.xe_id = x.id
               JOIN loai_xe lx ON x.loai_xe_id = lx.id
               LEFT JOIN danh_gia dg ON dt.id = dg.don_thue_xe_id
               WHERE dt.khach_hang_id = ? AND dt.trang_thai = 'da_tra_xe'
               ORDER BY dt.id DESC";
$orders = db_select($orders_sql, [$_SESSION['customer_id']]);

// Lấy danh sách đánh giá đã gửi - sử dụng * để tránh lỗi cột
$ratings_sql = "SELECT dg.*, dt.ngay_bat_dau, dt.ngay_ket_thuc, x.ten_xe, lx.ten_loai
                FROM danh_gia dg
                JOIN don_thue_xe dt ON dg.don_thue_xe_id = dt.id
                JOIN xe x ON dt.xe_id = x.id
                JOIN loai_xe lx ON x.loai_xe_id = lx.id
                WHERE dg.khach_hang_id = ?
                ORDER BY dg.ngay_danh_gia DESC";
$ratings = db_select($ratings_sql, [$_SESSION['customer_id']]);

include('header.php');
?>

<!-- Link CSS cho trang đánh giá -->
<link rel="stylesheet" type="text/css" href="<?= ROOT_PATH ?>/asset/css/rating.css">

<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-star"></i> Đánh giá dịch vụ thuê xe
                </h5>
            </div>
            <div class="card-body">
                <!-- Tab navigation -->
                <ul class="nav nav-tabs" id="ratingTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                            <i class="fas fa-clock"></i> Chờ đánh giá
                            <?php
                            $pending_count = count(array_filter($orders, function($o) { 
                                return empty($o['da_danh_gia']); 
                            }));
                            if ($pending_count > 0) echo "($pending_count)";
                            ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                            <i class="fas fa-check-circle"></i> Đã đánh giá
                            <?php if (count($ratings) > 0) echo "(" . count($ratings) . ")"; ?>
                        </button>
                    </li>
                </ul>
                
                <!-- Tab content -->
                <div class="tab-content mt-3" id="ratingTabsContent">
                    <!-- Tab chờ đánh giá -->
                    <div class="tab-pane fade show active" id="pending" role="tabpanel">
                        <?php
                        // Lọc đơn chưa đánh giá
                        $pending_orders = array_filter($orders, function($order) {
                            return empty($order['da_danh_gia']);
                        });
                        ?>
                        
                        <?php if (empty($pending_orders)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Không có đơn thuê xe nào cần đánh giá. 
                                <a href="xe.php" class="text-decoration-none">Thuê xe ngay</a>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($pending_orders as $order): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card rating-card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title text-primary">
                                                    <i class="fas fa-file-contract"></i>
                                                    Đơn thuê xe #<?= $order['id'] ?>
                                                </h6>
                                                <p class="card-text">
                                                    <i class="fas fa-car text-info"></i> 
                                                    <strong><?= htmlspecialchars($order['ten_xe']) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($order['ten_loai']) ?></small>
                                                </p>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar"></i>
                                                        <?= format_datetime($order['ngay_bat_dau']) ?>
                                                        <br>
                                                        đến <?= format_datetime($order['ngay_ket_thuc']) ?>
                                                        <br>
                                                        <i class="fas fa-money-bill text-success"></i> 
                                                        <?= format_money($order['tong_tien']) ?>
                                                    </small>
                                                </p>
                                                <div class="text-center">
                                                    <button class="btn btn-warning btn-sm" 
                                                            onclick="openRatingModal(<?= $order['id'] ?>, '<?= htmlspecialchars($order['ten_xe'], ENT_QUOTES) ?>', '<?= htmlspecialchars($order['ten_loai'], ENT_QUOTES) ?>')">
                                                        <i class="fas fa-star"></i> Đánh giá ngay
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Tab đã đánh giá -->
                    <div class="tab-pane fade" id="completed" role="tabpanel">
                        <?php if (empty($ratings)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Bạn chưa có đánh giá nào. Hãy thuê xe và chia sẻ trải nghiệm của bạn!
                            </div>
                        <?php else: ?>
                            <?php foreach ($ratings as $rating): ?>
                                <?php 
                                // Xử lý an toàn cho diem_so
                                $diem_so = 0;
                                if (isset($rating['diem_so'])) {
                                    $diem_so = (int)$rating['diem_so'];
                                } elseif (isset($rating['diem'])) {
                                    $diem_so = (int)$rating['diem'];
                                } elseif (isset($rating['rating'])) {
                                    $diem_so = (int)$rating['rating'];
                                }
                                
                                $rating_labels = [
                                    1 => 'Rất không hài lòng',
                                    2 => 'Không hài lòng', 
                                    3 => 'Bình thường',
                                    4 => 'Hài lòng',
                                    5 => 'Rất hài lòng'
                                ];
                                ?>
                                <div class="card mb-3 shadow-sm">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h6 class="card-title text-primary">
                                                    <i class="fas fa-file-contract"></i>
                                                    Đơn thuê xe #<?= $rating['don_thue_xe_id'] ?>
                                                </h6>
                                                <p class="card-text mb-2">
                                                    <i class="fas fa-car text-info"></i> 
                                                    <strong><?= htmlspecialchars($rating['ten_xe']) ?></strong>
                                                    - <?= htmlspecialchars($rating['ten_loai']) ?>
                                                </p>
                                                
                                                <div class="mb-3">
                                                    <div class="rating-display d-flex align-items-center">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="<?= $i <= $diem_so ? 'fas' : 'far' ?> fa-star text-warning me-1"></i>
                                                        <?php endfor; ?>
                                                        <span class="ms-2 fw-bold text-warning">
                                                            <?= $diem_so ?>/5 - <?= $rating_labels[$diem_so] ?? 'Chưa đánh giá' ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <?php if (!empty($rating['noi_dung'])): ?>
                                                    <div class="mb-3">
                                                        <strong class="text-dark">Nội dung đánh giá:</strong>
                                                        <div class="border-start border-primary ps-3 mt-1">
                                                            <p class="text-muted mb-0 fst-italic">
                                                                "<?= nl2br(htmlspecialchars($rating['noi_dung'])) ?>"
                                                            </p>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($rating['phan_hoi'])): ?>
                                                    <div class="alert alert-light border-start border-info">
                                                        <strong class="text-info">
                                                            <i class="fas fa-reply"></i> Phản hồi từ XeDeep:
                                                        </strong>
                                                        <p class="mb-0 mt-1">
                                                            <?= nl2br(htmlspecialchars($rating['phan_hoi'])) ?>
                                                        </p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-md-4 text-end">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i>
                                                    <?php
                                                    $ngay_danh_gia = $rating['ngay_danh_gia'] ?? 'Không xác định';
                                                    if ($ngay_danh_gia !== 'Không xác định') {
                                                        echo format_datetime($ngay_danh_gia);
                                                    } else {
                                                        echo $ngay_danh_gia;
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal đánh giá duy nhất -->
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="ratingForm">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-star text-warning"></i> 
                        <span id="modalTitle">Đánh giá đơn thuê xe</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="don_thue_xe_id" id="orderIdInput">
                    
                    <div class="mb-3">
                        <label class="form-label">Thông tin xe đã thuê:</label>
                        <div class="alert alert-light border" id="carInfo">
                            <i class="fas fa-car text-primary"></i>
                            <strong id="carName"></strong>
                            - <span id="carType"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Mức độ hài lòng <span class="text-danger">*</span>
                        </label>
                        <div class="rating-stars" data-rating-group="diem_so">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="rating-star" data-rating="<?= $i ?>">
                                    <input type="radio" name="diem_so" value="<?= $i ?>" required style="display: none;">
                                    <i class="fas fa-star"></i>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <div class="rating-text text-center mt-2" style="display: none;">
                            <small class="text-muted"></small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chia sẻ trải nghiệm</label>
                        <textarea name="noi_dung" class="form-control" rows="4" 
                                placeholder="Hãy chia sẻ cảm nhận của bạn về chất lượng xe, dịch vụ hỗ trợ..."></textarea>
                        <small class="text-muted">
                            Đánh giá của bạn sẽ giúp cải thiện chất lượng dịch vụ
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" name="submit_rating" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Gửi đánh giá
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript xử lý rating -->
<script src="<?= ROOT_PATH ?>/asset/js/rating.js"></script>

<script>
// Hàm mở modal đánh giá
function openRatingModal(orderId, carName, carType) {
    // Cập nhật thông tin trong modal
    document.getElementById('orderIdInput').value = orderId;
    document.getElementById('modalTitle').textContent = 'Đánh giá đơn thuê xe #' + orderId;
    document.getElementById('carName').textContent = carName;
    document.getElementById('carType').textContent = carType;
    
    // Reset form
    const form = document.getElementById('ratingForm');
    form.reset();
    
    // Reset rating stars
    const stars = document.querySelectorAll('.rating-star');
    stars.forEach(star => {
        star.classList.remove('selected', 'active');
    });
    
    // Reset rating text
    const ratingText = document.querySelector('.rating-text');
    if (ratingText) {
        ratingText.style.display = 'none';
        const small = ratingText.querySelector('small');
        if (small) small.textContent = '';
    }
    
    // Hiển thị modal
    const modal = new bootstrap.Modal(document.getElementById('ratingModal'));
    modal.show();
    
    // Re-initialize rating system khi modal được mở
    const modalElement = document.getElementById('ratingModal');
    modalElement.addEventListener('shown.bs.modal', function () {
        // Trigger rating initialization lại nếu cần
        const container = this.querySelector('.rating-stars');
        if (container && !container.dataset.initialized) {
            // Khởi tạo lại rating system
            const event = new CustomEvent('rating:reinitialize');
            container.dispatchEvent(event);
        }
    }, { once: true });
}

// Event listener cho việc reset modal khi đóng
document.addEventListener('DOMContentLoaded', function() {
    const ratingModal = document.getElementById('ratingModal');
    if (ratingModal) {
        ratingModal.addEventListener('hidden.bs.modal', function () {
            // Reset form hoàn toàn
            const form = this.querySelector('#ratingForm');
            if (form) {
                form.reset();
            }
            
            // Reset tất cả các trường
            document.getElementById('orderIdInput').value = '';
            document.getElementById('modalTitle').textContent = 'Đánh giá đơn thuê xe';
            document.getElementById('carName').textContent = '';
            document.getElementById('carType').textContent = '';
            
            // Reset rating stars
            const stars = this.querySelectorAll('.rating-star');
            stars.forEach(star => {
                star.classList.remove('selected', 'active', 'clicked');
            });
            
            // Reset rating text
            const ratingText = this.querySelector('.rating-text');
            if (ratingText) {
                ratingText.style.display = 'none';
                const small = ratingText.querySelector('small');
                if (small) small.textContent = '';
            }
        });
    }
});
</script>

<?php include('footer.php'); ?>