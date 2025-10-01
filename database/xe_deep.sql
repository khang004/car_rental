-- Script tạo database cho hệ thống quản lý thuê xe XeDeep
-- Tạo bởi: GitHub Copilot
-- Ngày tạo: 29/09/2025

-- Tạo database
CREATE DATABASE IF NOT EXISTS xe_deep CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE xe_deep;

-- Bảng loại xe
CREATE TABLE loai_xe (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ten_loai VARCHAR(100) NOT NULL COMMENT 'Tên loại xe (xe máy, ô tô 4 chỗ, ô tô 7 chỗ, ...)',
    mo_ta TEXT COMMENT 'Mô tả loại xe',
    trang_thai TINYINT DEFAULT 1 COMMENT '1: Hoạt động, 0: Ngừng hoạt động',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng xe
CREATE TABLE xe (
    id INT PRIMARY KEY AUTO_INCREMENT,
    loai_xe_id INT NOT NULL,
    bien_so VARCHAR(20) NOT NULL UNIQUE COMMENT 'Biển số xe',
    ten_xe VARCHAR(200) NOT NULL COMMENT 'Tên xe (Honda Wave, Toyota Vios, ...)',
    hang_xe VARCHAR(100) COMMENT 'Hãng xe',
    mau_sac VARCHAR(50) COMMENT 'Màu sắc',
    nam_san_xuat YEAR COMMENT 'Năm sản xuất',
    gia_thue_ngay DECIMAL(10,2) NOT NULL COMMENT 'Giá thuê theo ngày',
    gia_thue_gio DECIMAL(10,2) COMMENT 'Giá thuê theo giờ',
    hinh_anh VARCHAR(255) COMMENT 'Đường dẫn hình ảnh xe',
    mo_ta TEXT COMMENT 'Mô tả chi tiết xe',
    trang_thai ENUM('san_sang', 'dang_thue', 'bao_tri', 'ngung_hoat_dong') DEFAULT 'san_sang' COMMENT 'Trạng thái xe',
    so_km_hien_tai INT DEFAULT 0 COMMENT 'Số km hiện tại',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (loai_xe_id) REFERENCES loai_xe(id) ON DELETE RESTRICT
);

-- Bảng khách hàng (123456)
CREATE TABLE khach_hang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ho_ten VARCHAR(100) NOT NULL COMMENT 'Họ và tên',
    email VARCHAR(100) UNIQUE NOT NULL COMMENT 'Email đăng nhập',
    mat_khau VARCHAR(255) NOT NULL COMMENT 'Mật khẩu đã mã hóa',
    so_dien_thoai VARCHAR(15) COMMENT 'Số điện thoại',
    dia_chi TEXT COMMENT 'Địa chỉ',
    so_cccd VARCHAR(20) COMMENT 'Số căn cước công dân',
    ngay_sinh DATE COMMENT 'Ngày sinh',
    gioi_tinh ENUM('nam', 'nu', 'khac') COMMENT 'Giới tính',
    bang_lai VARCHAR(20) COMMENT 'Số bằng lái xe',
    avatar VARCHAR(255) COMMENT 'Ảnh đại diện',
    trang_thai TINYINT DEFAULT 1 COMMENT '1: Hoạt động, 0: Bị khóa',
    ngay_dang_ky TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng quản trị viên (admin123)
CREATE TABLE quan_tri_vien (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ho_ten VARCHAR(100) NOT NULL COMMENT 'Họ và tên',
    email VARCHAR(100) UNIQUE NOT NULL COMMENT 'Email đăng nhập',
    mat_khau VARCHAR(255) NOT NULL COMMENT 'Mật khẩu đã mã hóa',
    so_dien_thoai VARCHAR(15) COMMENT 'Số điện thoại',
    vai_tro ENUM('admin', 'nhan_vien') DEFAULT 'nhan_vien' COMMENT 'Vai trò',
    avatar VARCHAR(255) COMMENT 'Ảnh đại diện',
    trang_thai TINYINT DEFAULT 1 COMMENT '1: Hoạt động, 0: Ngừng hoạt động',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng đơn thuê xe
CREATE TABLE don_thue_xe (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ma_don VARCHAR(20) UNIQUE NOT NULL COMMENT 'Mã đơn thuê',
    khach_hang_id INT NOT NULL,
    xe_id INT NOT NULL,
    loai_thue ENUM('theo_gio', 'theo_ngay') DEFAULT 'theo_ngay' COMMENT 'Loại thuê',
    ngay_bat_dau DATETIME NOT NULL COMMENT 'Ngày giờ bắt đầu thuê',
    ngay_ket_thuc DATETIME NOT NULL COMMENT 'Ngày giờ kết thúc thuê',
    dia_chi_nhan VARCHAR(255) COMMENT 'Địa chỉ nhận xe',
    dia_chi_tra VARCHAR(255) COMMENT 'Địa chỉ trả xe',
    gia_thue DECIMAL(10,2) NOT NULL COMMENT 'Giá thuê',
    phi_phu_thu DECIMAL(10,2) DEFAULT 0 COMMENT 'Phí phụ thu (nếu có)',
    tong_tien DECIMAL(10,2) NOT NULL COMMENT 'Tổng tiền',
    tien_dat_coc DECIMAL(10,2) DEFAULT 0 COMMENT 'Tiền đặt cọc',
    phuong_thuc_thanh_toan ENUM('tien_mat', 'chuyen_khoan', 'the_tin_dung') DEFAULT 'tien_mat',
    trang_thai ENUM('cho_xac_nhan', 'da_xac_nhan', 'dang_thue', 'da_tra_xe', 'da_huy', 'qua_han') DEFAULT 'cho_xac_nhan',
    ghi_chu TEXT COMMENT 'Ghi chú',
    so_km_bat_dau INT COMMENT 'Số km khi nhận xe',
    so_km_ket_thuc INT COMMENT 'Số km khi trả xe',
    nguoi_xac_nhan INT COMMENT 'ID nhân viên xác nhận',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (khach_hang_id) REFERENCES khach_hang(id) ON DELETE CASCADE,
    FOREIGN KEY (xe_id) REFERENCES xe(id) ON DELETE RESTRICT,
    FOREIGN KEY (nguoi_xac_nhan) REFERENCES quan_tri_vien(id) ON DELETE SET NULL
);

-- Bảng đánh giá dịch vụ
CREATE TABLE danh_gia (
    id INT PRIMARY KEY AUTO_INCREMENT,
    don_thue_xe_id INT NOT NULL,
    khach_hang_id INT NOT NULL,
    diem_so TINYINT NOT NULL COMMENT 'Điểm đánh giá từ 1-5',
    noi_dung TEXT COMMENT 'Nội dung đánh giá',
    phan_hoi TEXT COMMENT 'Phản hồi từ quản trị',
    ngay_danh_gia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (don_thue_xe_id) REFERENCES don_thue_xe(id) ON DELETE CASCADE,
    FOREIGN KEY (khach_hang_id) REFERENCES khach_hang(id) ON DELETE CASCADE
);

-- Bảng lịch sử bảo trì xe
CREATE TABLE bao_tri_xe (
    id INT PRIMARY KEY AUTO_INCREMENT,
    xe_id INT NOT NULL,
    loai_bao_tri VARCHAR(100) NOT NULL COMMENT 'Loại bảo trì (thay dầu, sửa chữa, ...)',
    mo_ta TEXT COMMENT 'Mô tả chi tiết',
    chi_phi DECIMAL(10,2) COMMENT 'Chi phí bảo trì',
    ngay_bao_tri DATE NOT NULL COMMENT 'Ngày bảo trì',
    garage VARCHAR(200) COMMENT 'Garage/cửa hàng bảo trì',
    nguoi_thuc_hien INT COMMENT 'Nhân viên thực hiện',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (xe_id) REFERENCES xe(id) ON DELETE CASCADE,
    FOREIGN KEY (nguoi_thuc_hien) REFERENCES quan_tri_vien(id) ON DELETE SET NULL
);

-- Bảng cấu hình hệ thống
CREATE TABLE cau_hinh (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ten_cau_hinh VARCHAR(100) NOT NULL UNIQUE COMMENT 'Tên cấu hình',
    gia_tri TEXT COMMENT 'Giá trị cấu hình',
    mo_ta VARCHAR(255) COMMENT 'Mô tả',
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Chèn dữ liệu mẫu
-- Loại xe
INSERT INTO loai_xe (ten_loai, mo_ta) VALUES
('Xe máy', 'Xe máy các loại (Honda, Yamaha, Suzuki...)'),
('Ô tô 4 chỗ', 'Ô tô sedan, hatchback 4-5 chỗ ngồi'),
('Ô tô 7 chỗ', 'Ô tô SUV, MPV 7 chỗ ngồi'),
('Xe tải nhỏ', 'Xe tải nhỏ dưới 1 tấn');

-- Quản trị viên mặc định (mật khẩu: admin123)
INSERT INTO quan_tri_vien (ho_ten, email, mat_khau, vai_tro) VALUES
('Quản trị viên', 'admin@xedeep.com', '$2y$10$Tqks/w0K8lYGqV5R3oPJOuE4MFgFbXWrKQKjxDVNqJWHq5H6O3sou', 'admin'),
('Nhân viên', 'nhanvien@xedeep.com', '$2y$10$AeZOFHSB4aBKvhmdTkkWHOQSj6dQOpZsQ2uHxDJKGKJHhSgBtKgmu', 'nhan_vien');

-- Khách hàng mẫu (mật khẩu: 123456)
INSERT INTO khach_hang (ho_ten, email, mat_khau, so_dien_thoai, dia_chi) VALUES
('Nguyễn Văn A', 'khach1@email.com', '$2y$10$XQovF4JglHn4Oi1uJzLhwu6wBOjOhA7lBSFJMbQq6G7oVXn8JuVbu', '0987654321', '123 Đường XYZ, Q.1, TP.HCM'),
('Trần Thị B', 'khach2@email.com', '$2y$10$XQovF4JglHn4Oi1uJzLhwu6wBOjOhA7lBSFJMbQq6G7oVXn8JuVbu', '0976543210', '456 Đường ABC, Q.2, TP.HCM');

-- Xe mẫu
INSERT INTO xe (loai_xe_id, bien_so, ten_xe, hang_xe, mau_sac, nam_san_xuat, gia_thue_ngay, gia_thue_gio, mo_ta) VALUES
(1, '29A1-12345', 'Honda Wave Alpha', 'Honda', 'Đỏ', 2022, 150000, 20000, 'Xe máy Honda Wave Alpha màu đỏ, mới 2022'),
(1, '29B2-67890', 'Yamaha Exciter', 'Yamaha', 'Xanh', 2023, 180000, 25000, 'Xe máy Yamaha Exciter màu xanh, mới 2023'),
(2, '29C3-11111', 'Toyota Vios', 'Toyota', 'Trắng', 2021, 800000, 100000, 'Ô tô Toyota Vios màu trắng, 4 chỗ ngồi'),
(2, '29D4-22222', 'Honda City', 'Honda', 'Bạc', 2022, 750000, 95000, 'Ô tô Honda City màu bạc, 4 chỗ ngồi'),
(3, '29E5-33333', 'Toyota Innova', 'Toyota', 'Đen', 2020, 1200000, 150000, 'Ô tô Toyota Innova màu đen, 7 chỗ ngồi');

-- Cấu hình hệ thống
INSERT INTO cau_hinh (ten_cau_hinh, gia_tri, mo_ta) VALUES
('ten_cong_ty', 'XeDeep', 'Tên công ty'),
('dia_chi', '123 Đường ABC, Quận 1, TP.HCM', 'Địa chỉ công ty'),
('so_dien_thoai', '0901234567', 'Số điện thoại liên hệ'),
('email', 'info@xedeep.com', 'Email liên hệ'),
('tien_dat_coc_mac_dinh', '500000', 'Tiền đặt cọc mặc định'),
('thoi_gian_huy_don', '24', 'Thời gian cho phép hủy đơn (giờ)');

-- Tạo các chỉ mục để tối ưu hóa truy vấn
CREATE INDEX idx_xe_loai_xe_id ON xe(loai_xe_id);
CREATE INDEX idx_xe_trang_thai ON xe(trang_thai);
CREATE INDEX idx_don_thue_xe_khach_hang_id ON don_thue_xe(khach_hang_id);
CREATE INDEX idx_don_thue_xe_xe_id ON don_thue_xe(xe_id);
CREATE INDEX idx_don_thue_xe_trang_thai ON don_thue_xe(trang_thai);
CREATE INDEX idx_don_thue_xe_ngay_bat_dau ON don_thue_xe(ngay_bat_dau);
CREATE INDEX idx_don_thue_xe_ngay_ket_thuc ON don_thue_xe(ngay_ket_thuc);
CREATE INDEX idx_danh_gia_don_thue_xe_id ON danh_gia(don_thue_xe_id);
CREATE INDEX idx_bao_tri_xe_xe_id ON bao_tri_xe(xe_id);