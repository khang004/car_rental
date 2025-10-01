<?php
include(__DIR__ . "/../admin_common.php");

// Kiểm tra đăng nhập admin
check_admin_login();

// Lấy tham số tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$rating_filter = isset($_GET['rating']) ? $_GET['rating'] : '';

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Xây dựng câu truy vấn
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(kh.ho_ten LIKE ? OR dg.noi_dung LIKE ? OR CONCAT('DH', LPAD(dt.id, 6, '0')) LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($rating_filter)) {
    $where_conditions[] = "dg.diem_so = ?";
    $params[] = $rating_filter;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Truy vấn tổng số bản ghi
$count_sql = "SELECT COUNT(*) as total 
              FROM danh_gia dg
              INNER JOIN khach_hang kh ON dg.khach_hang_id = kh.id
              INNER JOIN don_thue_xe dt ON dg.don_thue_xe_id = dt.id
              INNER JOIN xe x ON dt.xe_id = x.id
              $where_clause";

$total_result = db_select($count_sql, $params);
$total_records = $total_result[0]['total'];
$total_pages = ceil($total_records / $limit);

// Truy vấn dữ liệu - đã loại bỏ cột bien_so
$sql = "SELECT dg.*, 
               kh.ho_ten as ten_khach_hang,
               kh.email as email_khach_hang,
               CONCAT('DH', LPAD(dt.id, 6, '0')) as ma_don,
               x.ten_xe,
               x.id as xe_id,
               DATE_FORMAT(dg.ngay_danh_gia, '%d/%m/%Y %H:%i') as ngay_danh_gia_formatted
        FROM danh_gia dg
        INNER JOIN khach_hang kh ON dg.khach_hang_id = kh.id
        INNER JOIN don_thue_xe dt ON dg.don_thue_xe_id = dt.id
        INNER JOIN xe x ON dt.xe_id = x.id
        $where_clause
        ORDER BY dg.ngay_danh_gia DESC
        LIMIT $limit OFFSET $offset";

$reviews = db_select($sql, $params);

include(__DIR__ . "/../header.php");
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-star"></i> Quản lý đánh giá dịch vụ
                </h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary">Tổng: <?= number_format($total_records) ?> đánh giá</span>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Form tìm kiếm -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Tìm theo tên khách hàng, nội dung, mã đơn..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="rating" class="form-select">
                            <option value="">Tất cả điểm</option>
                            <option value="5" <?= $rating_filter == '5' ? 'selected' : '' ?>>5 sao</option>
                            <option value="4" <?= $rating_filter == '4' ? 'selected' : '' ?>>4 sao</option>
                            <option value="3" <?= $rating_filter == '3' ? 'selected' : '' ?>>3 sao</option>
                            <option value="2" <?= $rating_filter == '2' ? 'selected' : '' ?>>2 sao</option>
                            <option value="1" <?= $rating_filter == '1' ? 'selected' : '' ?>>1 sao</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="<?= ROOT_PATH ?>/admin/danh_gia/" class="btn btn-secondary">
                            <i class="fas fa-refresh"></i> Làm mới
                        </a>
                    </div>
                </form>

                <?php if (empty($reviews)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-star fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Không có đánh giá nào</h5>
                        <p class="text-muted">Chưa có khách hàng nào đánh giá dịch vụ.</p>
                    </div>
                <?php else: ?>
                    <!-- Bảng danh sách đánh giá -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="8%">ID</th>
                                    <th width="15%">Khách hàng</th>
                                    <th width="12%">Mã đơn</th>
                                    <th width="15%">Xe</th>
                                    <th width="10%">Điểm</th>
                                    <th width="25%">Nội dung</th>
                                    <th width="12%">Ngày đánh giá</th>
                                    <th width="8%">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td><?= $review['id'] ?></td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($review['ten_khach_hang']) ?></strong>
                                        </div>
                                        <small class="text-muted"><?= htmlspecialchars($review['email_khach_hang']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($review['ma_don']) ?></span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($review['ten_xe']) ?></strong>
                                        </div>
                                        <small class="text-muted">ID: <?= $review['xe_id'] ?></small>
                                    </td>
                                    <td>
                                        <div class="rating-stars">
                                            <?php 
                                            $rating = (int)$review['diem_so'];
                                            for ($i = 1; $i <= 5; $i++): 
                                            ?>
                                                <i class="fas fa-star <?= $i <= $rating ? 'text-warning' : 'text-muted' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <small class="text-muted"><?= $rating ?>/5</small>
                                    </td>
                                    <td>
                                        <div class="review-content">
                                            <?php 
                                            $content = htmlspecialchars($review['noi_dung']);
                                            echo strlen($content) > 100 ? substr($content, 0, 100) . '...' : $content;
                                            ?>
                                        </div>
                                        <?php if (!empty($review['phan_hoi'])): ?>
                                            <div class="mt-2">
                                                <small class="badge bg-success">Đã phản hồi</small>
                                            </div>
                                        <?php else: ?>
                                            <div class="mt-2">
                                                <small class="badge bg-warning">Chưa phản hồi</small>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $review['ngay_danh_gia_formatted'] ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="viewReview(<?= $review['id'] ?>)"
                                                    title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (empty($review['phan_hoi'])): ?>
                                            <button type="button" class="btn btn-outline-success" 
                                                    onclick="replyReview(<?= $review['id'] ?>)"
                                                    title="Phản hồi">
                                                <i class="fas fa-reply"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&rating=<?= urlencode($rating_filter) ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $page + 2);
                                
                                for ($i = $start; $i <= $end; $i++):
                                ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&rating=<?= urlencode($rating_filter) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&rating=<?= urlencode($rating_filter) ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal xem chi tiết đánh giá -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">Chi tiết đánh giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="reviewModalBody">
                <!-- Nội dung được tải bằng AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Modal phản hồi đánh giá -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="replyModalLabel">Phản hồi đánh giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="replyForm">
                <div class="modal-body">
                    <input type="hidden" id="reviewId" name="review_id">
                    <div class="mb-3">
                        <label for="replyContent" class="form-label">
                            Nội dung phản hồi <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="replyContent" name="phan_hoi" rows="4" 
                                  placeholder="Nhập phản hồi cho đánh giá này..." 
                                  required maxlength="1000"></textarea>
                        <div class="form-text">
                            <span id="charCount">0</span>/1000 ký tự
                        </div>
                        <div class="invalid-feedback">
                            Vui lòng nhập nội dung phản hồi (tối đa 1000 ký tự).
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-info me-2" onclick="testConnection()">Test</button>
                    <button type="submit" class="btn btn-primary">Gửi phản hồi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Xem chi tiết đánh giá
function viewReview(id) {
    fetch(`view.php?id=${id}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('reviewModalBody').innerHTML = html;
            new bootstrap.Modal(document.getElementById('reviewModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi tải thông tin đánh giá');
        });
}

// Phản hồi đánh giá
function replyReview(id) {
    document.getElementById('reviewId').value = id;
    document.getElementById('replyContent').value = '';
    
    // Reset form validation state
    const form = document.getElementById('replyForm');
    form.classList.remove('was-validated');
    
    // Hiển thị modal
    const modal = new bootstrap.Modal(document.getElementById('replyModal'));
    modal.show();
    
    // Focus vào textarea khi modal hiển thị
    document.getElementById('replyModal').addEventListener('shown.bs.modal', function () {
        document.getElementById('replyContent').focus();
    }, { once: true });
}

// Xử lý form phản hồi - đảm bảo chạy khi DOM ready
document.addEventListener('DOMContentLoaded', function() {
    const replyForm = document.getElementById('replyForm');
    if (replyForm) {
        replyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted'); // Debug log
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            const reviewId = document.getElementById('reviewId').value;
            const replyContent = document.getElementById('replyContent').value.trim();
            
            // Validation
            if (!reviewId || reviewId <= 0) {
                alert('ID đánh giá không hợp lệ');
                return;
            }
            
            if (!replyContent) {
                alert('Vui lòng nhập nội dung phản hồi');
                document.getElementById('replyContent').focus();
                return;
            }
            
            if (replyContent.length > 1000) {
                alert('Nội dung phản hồi không được vượt quá 1000 ký tự');
                return;
            }
            
            // Disable button và hiển thị loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
            
            const formData = new FormData(this);
            console.log('Sending data:', {
                review_id: formData.get('review_id'),
                phan_hoi: formData.get('phan_hoi')
            }); // Debug log
            
            fetch('reply.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Response status:', response.status); // Debug log
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log('Response text:', text); // Debug log
                // Cố gắng parse JSON, nếu không được thì log lỗi
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed data:', data); // Debug log
                    
                    if (data.success) {
                        // Đóng modal
                        const modalElement = document.getElementById('replyModal');
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        } else {
                            // Fallback nếu getInstance không hoạt động
                            modalElement.classList.remove('show');
                            document.body.classList.remove('modal-open');
                            const backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) backdrop.remove();
                        }
                        
                        // Hiển thị thông báo thành công
                        alert('Phản hồi đã được gửi thành công!');
                        
                        // Reload trang để cập nhật trạng thái
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    } else {
                        alert('Có lỗi xảy ra: ' + data.message);
                    }
                } catch (jsonError) {
                    console.error('JSON Parse Error:', jsonError);
                    console.error('Response text:', text);
                    alert('Có lỗi xảy ra khi xử lý phản hồi từ server: ' + text);
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                alert('Có lỗi xảy ra khi gửi phản hồi: ' + error.message);
            })
            .finally(() => {
                // Khôi phục button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});

// Đếm ký tự cho textarea
document.getElementById('replyContent').addEventListener('input', function() {
    const charCount = this.value.length;
    const charCountElement = document.getElementById('charCount');
    charCountElement.textContent = charCount;
    
    // Thay đổi màu khi gần đạt giới hạn
    if (charCount > 900) {
        charCountElement.style.color = '#dc3545'; // Đỏ
    } else if (charCount > 700) {
        charCountElement.style.color = '#fd7e14'; // Cam
    } else {
        charCountElement.style.color = '#6c757d'; // Xám
    }
});

// Reset character count khi modal đóng
document.getElementById('replyModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('charCount').textContent = '0';
    document.getElementById('charCount').style.color = '#6c757d';
});

// Hàm test kết nối
function testConnection() {
    const reviewId = document.getElementById('reviewId').value;
    console.log('Testing with review ID:', reviewId);
    
    fetch('reply.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'review_id=' + reviewId + '&phan_hoi=Test phản hồi'
    })
    .then(response => response.text())
    .then(text => {
        console.log('Test response:', text);
        alert('Test response: ' + text);
    })
    .catch(error => {
        console.error('Test error:', error);
        alert('Test error: ' + error.message);
    });
}
</script>

<?php include(__DIR__ . "/../footer.php"); ?>