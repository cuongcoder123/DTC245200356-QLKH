-- Database: inventory_db
-- He thong Quan Ly Kho Hang (Inventory Management System)
-- Ma de: 05 - DTC245200356

CREATE DATABASE IF NOT EXISTS inventory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventory_db;

-- 1. Bang users: Quan ly nguoi dung va phan quyen
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'staff') DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bang categories: Danh muc san pham
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bang suppliers: Nha cung cap
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bang products: Hang hoa trong kho
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    category_id INT NULL,
    unit VARCHAR(20) DEFAULT 'Chiếc',
    purchase_price DECIMAL(15,2) DEFAULT 0,
    sale_price DECIMAL(15,2) DEFAULT 0,
    stock_quantity INT DEFAULT 0,
    min_stock INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bang inventory_transactions: Lich su giao dich Nhap / Xuat kho
CREATE TABLE IF NOT EXISTS inventory_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('IMPORT', 'EXPORT') NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    supplier_id INT NULL,
    note TEXT,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bang system_logs: Luu tru nhat ky he thong
CREATE TABLE IF NOT EXISTS system_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level VARCHAR(10) NOT NULL DEFAULT 'INFO',
    action VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEED DATA
-- Users: admin (pass: Admin@123456), staff (pass: Staff@123456)
-- Password hash fallback: $2y$10$4y9pI0hD2Wq1qf2b7yPzeOPFzE9q74x4qXg1q/O6u1y6Jq8oQyJmi hoac Admin@123456
INSERT INTO users (id, username, password, full_name, email, role) VALUES
(1, 'admin', '$2y$10$wE3Q0j7Xh4P8cO2YjN6vxeYdKlXvX/Q5d8L2t5O4k0pB0wB8F2m6e', 'Quản trị viên Hệ thống', 'admin@warehouse.local', 'admin'),
(2, 'staff', '$2y$10$wE3Q0j7Xh4P8cO2YjN6vxeYdKlXvX/Q5d8L2t5O4k0pB0wB8F2m6e', 'Nguyễn Văn Nhân (Thủ kho)', 'staff@warehouse.local', 'staff')
ON DUPLICATE KEY UPDATE username=username;

-- Categories
INSERT INTO categories (id, name, description) VALUES
(1, 'Linh kiện máy tính', 'Bộ vi xử lý, RAM, Ổ cứng SSD, Mainboard, VGA'),
(2, 'Thiết bị mạng & Viễn thông', 'Router, Switch mạng, Thiết bị WiFi, Cáp mạng Cat6'),
(3, 'Thiết bị văn phòng', 'Máy in, Máy scan, Máy hủy tài liệu, Máy chiếu'),
(4, 'Phụ kiện & Ngoại vi', 'Bàn phím cơ, Chuột không dây, Tai nghe, Webcam'),
(5, 'Thiết bị lưu trữ & Nguồn điện', 'Bộ lưu điện UPS, Ổ cứng di động, Thẻ nhớ SD')
ON DUPLICATE KEY UPDATE name=name;

-- Suppliers
INSERT INTO suppliers (id, name, phone, email, address) VALUES
(1, 'Công ty TNHH Tin học Sao Mai', '02438889999', 'saomai@techvn.com', 'Tòa nhà Sao Mai, 19 Duy Tân, Cầu Giấy, Hà Nội'),
(2, 'Nhà Phân Phối FPT Synnex', '02473006666', 'contact@synnexfpt.com.vn', 'Tòa nhà FPT, Phố Duy Tân, Dịch Vọng Hậu, Cầu Giấy, Hà Nội'),
(3, 'Cisco Systems Việt Nam', '02838234567', 'sales@cisco.vn', 'Tầng 12, Saigon Centre, 65 Lê Lợi, Quận 1, TP. HCM'),
(4, 'Tập đoàn Công nghệ DGW', '02839290059', 'info@digiworld.com.vn', '195 Cô Bắc, Phường Cô Giang, Quận 1, TP. HCM'),
(5, 'Công ty TNHH Công nghệ Viễn Đông', '02437654321', 'viendong@tech.vn', 'Số 45 Nguyễn Khang, Yên Hòa, Cầu Giấy, Hà Nội')
ON DUPLICATE KEY UPDATE name=name;

-- Products
INSERT INTO products (id, product_code, name, category_id, unit, purchase_price, sale_price, stock_quantity, min_stock) VALUES
(1, 'SP-CPU-I7', 'Intel Core i7-14700K (33M Cache, up to 5.60 GHz)', 1, 'Chiếc', 9500000, 10800000, 24, 5),
(2, 'SP-RAM-16G', 'Kingston Fury Beast 16GB DDR5 5600MHz', 1, 'Thanh', 1250000, 1550000, 68, 10),
(3, 'SP-SSD-1TB', 'Samsung 990 Pro 1TB PCIe Gen 4.0 NVMe', 1, 'Chiếc', 2400000, 2850000, 42, 8),
(4, 'SP-SW-24P', 'Switch Cisco CBS250-24T-4G 24 Port Gigabit', 2, 'Bộ', 6200000, 7500000, 12, 3),
(5, 'SP-RTR-AX3000', 'Router WiFi 6 Ruijie Reyee RG-EW3200GX PRO', 2, 'Bộ', 1450000, 1850000, 3, 5), -- Cảnh báo sắp hết
(6, 'SP-CAB-CAT6', 'Thùng cáp mạng CommScope AMP Cat6 UTP 305m', 2, 'Thùng', 2150000, 2600000, 18, 5),
(7, 'SP-PRN-HP107W', 'Máy in Laser đơn năng HP Neverstop 1000w', 3, 'Chiếc', 3200000, 3750000, 8, 4),
(8, 'SP-SCN-CANON', 'Máy quét tài liệu Canon DR-C225 II High Speed', 3, 'Chiếc', 8900000, 10200000, 2, 3), -- Cảnh báo sắp hết
(9, 'SP-KB-LOGI', 'Bàn phím cơ không dây Logitech MX Mechanical', 4, 'Chiếc', 2900000, 3450000, 35, 6),
(10, 'SP-MS-MASTER3', 'Chuột không dây Logitech MX Master 3S', 4, 'Chiếc', 1950000, 2350000, 4, 8), -- Cảnh báo sắp hết
(11, 'SP-CAM-BRIO', 'Webcam 4K Ultra HD Logitech Brio 4K Pro', 4, 'Chiếc', 3800000, 4400000, 15, 4),
(12, 'SP-UPS-1000VA', 'Bộ lưu điện APC Easy UPS BVX 1200VA 230V', 5, 'Bộ', 2100000, 2550000, 16, 5),
(13, 'SP-HDD-EXT-2T', 'Ổ cứng di động WD My Passport 2TB USB 3.2', 5, 'Chiếc', 1700000, 2050000, 29, 6)
ON DUPLICATE KEY UPDATE name=name;

-- Sample Transactions
INSERT INTO inventory_transactions (id, transaction_code, type, product_id, quantity, unit_price, total_amount, supplier_id, note, created_by, created_at) VALUES
(1, 'NK-20260901-001', 'IMPORT', 1, 30, 9500000, 285000000, 2, 'Nhập lô CPU Intel Gen 14 đợt đầu tháng 9', 1, '2026-09-01 08:30:00'),
(2, 'NK-20260902-002', 'IMPORT', 2, 80, 1250000, 100000000, 1, 'Nhập bổ sung RAM Kingston DDR5', 1, '2026-09-02 09:15:00'),
(3, 'NK-20260905-003', 'IMPORT', 3, 50, 2400000, 120000000, 4, 'Nhập lô ổ cứng SSD Samsung 990 Pro', 1, '2026-09-05 10:00:00'),
(4, 'XK-20260910-001', 'EXPORT', 1, 6, 10800000, 64800000, NULL, 'Xuất kho giao cho dự án phòng lab ĐH Bách Khoa', 2, '2026-09-10 14:20:00'),
(5, 'XK-20260912-002', 'EXPORT', 2, 12, 1550000, 18600000, NULL, 'Xuất kho cho khách hàng VIP Công ty VinaTech', 2, '2026-09-12 15:45:00'),
(6, 'NK-20260915-004', 'IMPORT', 4, 15, 6200000, 93000000, 3, 'Nhập thiết bị mạng Cisco từ NPP Cisco VN', 1, '2026-09-15 11:10:00'),
(7, 'XK-20260918-003', 'EXPORT', 4, 3, 7500000, 22500000, NULL, 'Xuất hạ tầng Switch cho văn phòng chi nhánh Cầu Giấy', 2, '2026-09-18 16:30:00'),
(8, 'XK-20260920-004', 'EXPORT', 3, 8, 2850000, 22800000, NULL, 'Xuất linh kiện nâng cấp máy chủ phòng IT', 2, '2026-09-20 09:40:00'),
(9, 'NK-20260922-005', 'IMPORT', 12, 20, 2100000, 42000000, 5, 'Nhập bổ sung Bộ lưu điện UPS dự phòng mất điện', 1, '2026-09-22 13:15:00'),
(10, 'XK-20260925-005', 'EXPORT', 12, 4, 2550000, 10200000, NULL, 'Xuất UPS lắp đặt hệ thống Camera giám sát', 2, '2026-09-25 10:50:00')
ON DUPLICATE KEY UPDATE transaction_code=transaction_code;

-- Logs
INSERT INTO system_logs (level, action, message, user_id, ip_address) VALUES
('INFO', 'SYSTEM_INIT', 'Hệ thống Quản lý kho hàng khởi tạo thành công CSDL.', 1, '127.0.0.1'),
('INFO', 'AUTH_LOGIN', 'Người dùng admin đăng nhập thành công vào hệ thống.', 1, '127.0.0.1');
