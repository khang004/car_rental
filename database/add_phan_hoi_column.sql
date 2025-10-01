-- Script thêm cột phan_hoi vào bảng danh_gia nếu chưa tồn tại
-- Chạy script này nếu gặp lỗi "unknown column phan_hoi"

USE xe_deep;

-- Kiểm tra và thêm cột phan_hoi nếu chưa tồn tại
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'xe_deep' 
  AND TABLE_NAME = 'danh_gia' 
  AND COLUMN_NAME = 'phan_hoi';

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE danh_gia ADD COLUMN phan_hoi TEXT COMMENT "Phản hồi từ quản trị" AFTER noi_dung',
    'SELECT "Column phan_hoi already exists" as message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Hiển thị cấu trúc bảng để xác nhận
DESCRIBE danh_gia;