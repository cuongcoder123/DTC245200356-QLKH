# HỆ THỐNG QUẢN LÝ KHO HÀNG (SMART INVENTORY MANAGEMENT SYSTEM)
> **Đề số 05** | Học phần: Triển khai & Quản trị Hệ thống / DevOps / An toàn Hệ thống  
> Mã số sinh viên: **DTC245200356** | Repository: **cuongcoder123/DTC245200356-QLKH**

---

## 📌 1. TỔNG QUAN DỰ ÁN
Hệ thống **Smart Inventory Management** là giải pháp phần mềm quản lý kho hàng hiện đại, được thiết kế và triển khai theo chuẩn kiến trúc **DevOps & Site Reliability Engineering (SRE)**:
- **Ứng dụng Web Quản lý Kho**: Xử lý đầy đủ nghiệp vụ Nhập kho, Xuất kho (có ràng buộc tồn), Quản lý hàng hóa, Danh mục, Nhà cung cấp, Báo cáo thẩm định tài sản, in phiếu kho chuẩn hóa.
- **Cổng an toàn Reverse Proxy**: Nginx Gateway xử lý SSL/TLS HTTPS tự ký, chuyển hướng HTTP sang HTTPS, thiết lập đầy đủ HTTP Security Headers.
- **Hệ cơ sở dữ liệu**: MySQL 8.0 được cô lập an toàn trong mạng nội bộ (`backend-net`), đi kèm giao diện trực quan phpMyAdmin.
- **Giám sát thời gian thực (Monitoring)**: Prometheus + Grafana tự động thu thập chỉ số container, MySQL và host.
- **Quản lý Log tập trung (Logging)**: Grafana Loki + Promtail thu thập log Nginx và log ứng dụng có cấu trúc, hỗ trợ truy vấn LogQL chuyên sâu.
- **Gia cố bảo mật (Hardening)**: Network Isolation (3 subnet riêng biệt), Non-root user, Resource limits, Quản lý biến môi trường `.env`.

---

## 🏗️ 2. KIẾN TRÚC TỔNG THỂ HỆ THỐNG

```
[ Client / Web Browser ]
         │ (HTTP :80 -> HTTPS :443)
         ▼
┌────────────────────────────────────────────────────────┐
│             NGINX REVERSE PROXY GATEWAY                │
│       (SSL Termination & HTTP Security Headers)        │
└────────┬──────────────────┬──────────────────┬─────────┘
         │                  │                  │
   (Path: /)          (Path: /pma/)      (Path: /grafana/)
         │                  │                  │
         ▼                  ▼                  ▼
┌─────────────────┐ ┌───────────────┐ ┌──────────────────┐
│  INVENTORY WEB  │ │  phpMyAdmin   │ │     GRAFANA      │
│  (PHP 8.2 App)  │ │ (Database UI) │ │ (Dashboards &    │
└────────┬────────┘ └───────┬───────┘ │  LogQL Viewer)   │
         │                  │         └────────▲─────────┘
         │ (backend-net)    │                  │
         ▼                  ▼                  │
┌───────────────────────────────────┐          │
│       MySQL 8.0 DATABASE          │          │
│    (Chỉ nội bộ, không mở port)    │          │
└─────────────────┬─────────────────┘          │
                  │                            │
                  ▼                            │
┌───────────────────────────────────┐          │
│        GIÁM SÁT & LOGGING         │          │
│  • Prometheus (:9090) ────────────┼──────────┤
│  • MySQL Exporter (:9104)         │          │
│  • Node Exporter (:9100)          │          │
│  • Promtail (:9080) ──> Loki ─────┼──────────┘
└───────────────────────────────────┘
```

---

## 📁 3. CẤU TRÚC THƯ MỤC DỰ ÁN

```text
DTC245200356-QLKH/
├── docker-compose.yml           # Khởi tạo và liên kết 9 dịch vụ container
├── .env.example                 # Mẫu cấu hình biến môi trường
├── .env                         # Tham số bảo mật (CSDL, User, Password)
├── .gitignore                   # Loại trừ file nhạy cảm và volume
├── README.md                    # Hướng dẫn cài đặt và vận hành
├── mysql/
│   └── init.sql                 # Cấu trúc bảng và dữ liệu mẫu khởi tạo
├── nginx/
│   ├── nginx.conf               # Cấu hình chính của Nginx & log format
│   ├── conf.d/
│   │   └── default.conf         # Cấu hình Reverse Proxy, HTTPS, Security Headers
│   └── ssl/
│       ├── server.crt           # Chứng chỉ SSL X.509
│       └── server.key           # Private key 2048-bit
├── app/
│   ├── Dockerfile               # Build container Web PHP 8.2 + Apache
│   ├── php.ini                  # Tinh chỉnh bảo mật PHP
│   └── src/                     # Toàn bộ mã nguồn Website Kho Hàng
│       ├── config/db.php        # Kết nối CSDL MySQL qua PDO
│       ├── helpers/auth.php     # Xác thực, phân quyền Admin/Staff
│       ├── helpers/logger.php   # Ghi Structured JSON logs
│       ├── api/                 # Endpoint xử lý nghiệp vụ & Log Generator
│       ├── assets/ (css, js)    # Giao diện hiện đại, Chart.js, Theme Dark/Light
│       ├── views/               # Header, Footer, Navbar, Sidebar
│       ├── index.php            # Dashboard KPI & Biểu đồ
│       ├── products.php         # Quản lý hàng hóa, tồn kho, giá bán
│       ├── categories.php       # Quản lý danh mục hàng
│       ├── suppliers.php        # Quản lý đối tác nhà cung cấp
│       ├── transactions.php     # Nhập / Xuất kho & In phiếu kho
│       ├── reports.php          # Báo cáo thẩm định tài sản tồn kho
│       ├── system.php           # Bảng kiểm tra hạ tầng & Sandbox LogQL
│       ├── login.php            # Trang đăng nhập
│       └── logout.php           # Đăng xuất an toàn
├── prometheus/
│   └── prometheus.yml           # Cấu hình scrape chỉ số từ các exporter
├── promtail/
│   └── promtail-config.yml      # Cấu hình thu thập log Nginx và App
├── loki/
│   └── loki-config.yml          # Cấu hình lưu trữ và index cho Loki
└── grafana/
    └── provisioning/
        ├── datasources/         # Tự động nạp Prometheus & Loki
        └── dashboards/          # Tự động nạp Dashboard giám sát kho
```

---

## 🚀 4. HƯỚNG DẪN KHỞI CHẠY HỆ THỐNG

### Yêu cầu tiền đề:
- Cài đặt **Docker Desktop** (bản 20.10+ hoặc mới hơn).
- Hỗ trợ lệnh `docker compose` (Docker Compose V2).

### Các bước thực hiện:

#### Bước 1: Chuẩn bị file môi trường
```bash
# Tạo file .env từ file mẫu nếu chưa có
cp .env.example .env
```

#### Bước 2: Tạo chứng chỉ SSL tự ký (Self-signed Certificate)
*(Chứng chỉ đã được tạo sẵn trong thư mục `nginx/ssl/`. Nếu muốn sinh mới, chạy lệnh):*
```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout nginx/ssl/server.key \
  -out nginx/ssl/server.crt \
  -subj "/C=VN/ST=Hanoi/L=Hanoi/O=DTC245200356/CN=localhost"
```

#### Bước 3: Khởi chạy toàn bộ hệ thống bằng Docker Compose
```bash
docker compose up -d --build
```

#### Bước 4: Kiểm tra trạng thái các container
```bash
docker compose ps
```
*Tất cả 9 dịch vụ (`inventory_db`, `inventory_web`, `inventory_nginx`, `inventory_phpmyadmin`, `inventory_prometheus`, `inventory_grafana`, `inventory_loki`, `inventory_promtail`, `inventory_mysql_exporter`) đều phải ở trạng thái **running (healthy)**.*

---

## 🌐 5. BẢNG THÔNG TIN TRUY CẬP DỊCH VỤ

| Dịch Vụ | Địa Chỉ Truy Cập (URL) | Tài Khoản Đăng Nhập | Ghi Chú |
| :--- | :--- | :--- | :--- |
| **Website Quản Lý Kho** | `https://localhost/` | `admin` / `Admin@123456`<br>`staff` / `Staff@123456` | Tự động chuyển hướng từ `http://localhost` sang HTTPS |
| **phpMyAdmin** | `https://localhost/pma/` | User: `inventory_user`<br>Pass: `inventory_pass_2026`<br>*(Hoặc root / RootSecret@2026!)* | Quản trị CSDL MySQL trực quan |
| **Grafana Dashboard** | `https://localhost/grafana/` | `admin` / `Admin@123456` | Dashboard thời gian thực & Truy vấn LogQL |
| **Prometheus Targets** | `https://localhost/prometheus/targets` | *(Không yêu cầu mật khẩu)* | Kiểm tra trạng thái các exporter chỉ số |
| **Loki Service** | `http://localhost:3100` *(Internal)* | *(API push & query)* | Nhận log từ Promtail qua cổng 3100 |

---

## 📸 5. HÌNH ẢNH GIAO DIỆN & TÍNH NĂNG HỆ THỐNG

### 5.1. Dashboard Tổng Quan & Quản Lý Kho Hàng
![Bảng Điều Khiển Tổng Quan Dashboard](docs/images/02_tong_quan_dashboard.png)

### 5.2. Quản Lý Danh Mục Hàng Hóa & Trạng Thái Tồn
![Quản Lý Hàng Hóa](docs/images/03_quan_ly_hang_hoa.png)

### 5.3. Quản Lý Giao Dịch Nhập / Xuất Kho
![Giao Dịch Nhập / Xuất Kho](docs/images/06_giao_dich_nhap_xuat.png)

### 5.4. Báo Cáo Thẩm Định & Thống Kê Tài Sản Tồn Kho
![Báo Cáo Thẩm Định Tồn Kho](docs/images/07_bao_cao_thong_ke.png)

### 5.5. Trung Tâm Giám Sát DevOps & Sandbox LogQL
![Trung Tâm Giám Sát DevOps & Sandbox LogQL](docs/images/08_trung_tam_giam_sat_logql.png)

### 5.6. Bảng Giám Sát Hạ Tầng & Logs Trên Grafana
![Grafana Dashboard](docs/images/09_grafana_dashboard.png)

### 5.7. Giám Sát Trạng Thái Các Exporters Trên Prometheus
![Prometheus Targets](docs/images/11_prometheus_targets.png)

---

## 🔍 6. KIỂM THỬ TRUY VẤN LOGQL VỚI LOKI TRÊN GRAFANA

Hệ thống đã tích hợp sẵn công cụ **Log Generator Sandbox** tại trang: `https://localhost/system.php`.  
Người dùng chỉ cần bấm nút để sinh ngay các log mẫu và kiểm tra trên Grafana (`Explore` -> Chọn DataSource **Loki**):

### Query 1: Lọc tất cả log lỗi HTTP (Mã 4xx hoặc 5xx) từ Nginx
```logql
{job="nginx"} |= "status=" |~ "status=[45][0-9][0-9]"
```
*Ý nghĩa: Phát hiện tức thì các truy vấn bị lỗi 403, 404 hoặc lỗi máy chủ backend 500.*

### Query 2: Thống kê tần suất request theo IP (Phát hiện DDoS / Brute Force)
```logql
sum by (client_ip) (count_over_time({job="nginx"} [5m]))
```
*Ý nghĩa: Giám sát lưu lượng truy cập từ từng client IP trong 5 phút.*

### Query 3: Tìm kiếm log lỗi giao dịch kho hàng
```logql
{job="app"} |= "inventory_transactions" |= "ERROR"
```
*Ý nghĩa: Truy vấn trực tiếp các lỗi xuất vượt quá số lượng tồn kho hoặc lỗi CSDL phát sinh.*

---

## 🛡️ 7. CÁC BIỆN PHÁP GIA CỐ BẢO MẬT (HARDENING)

1. **Non-Root Execution**: Container Web app chạy bằng tài khoản người dùng hạn chế (`appuser` UID 1001), ngăn chặn rủi ro Container Breakout.
2. **Network Isolation**:
   - `backend-net`: Cô lập database MySQL và exporter, không mở port ra máy chủ Host.
   - `frontend-net`: Kết nối Nginx với Web App và phpMyAdmin.
   - `monitoring-net`: Kết nối các công cụ giám sát Prometheus, Grafana, Loki.
3. **HTTP Security Headers**: Thiết lập đầy đủ `X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`, `Referrer-Policy`, và `Content-Security-Policy`.
4. **Bảo mật Thông tin Xác thực**: Không ghi mật khẩu trong mã nguồn; quản lý tập trung bằng `.env` và bảo vệ bằng `.gitignore`.
5. **Giới hạn Tài nguyên (Resource Limits)**: Thiết lập ngưỡng trần CPU và RAM cho từng container để phòng chống tấn công từ chối dịch vụ (DoS).

---

## 📜 8. NHẬT KÝ GIT COMMIT (THEO TIẾN ĐỘ ĐỀ BÀI)
- `Initial commit`: Khởi tạo cấu trúc repository.
- `Commit 1: Cau hinh Nginx Reverse Proxy, HTTPS Self-Signed va Security Headers`: Thiết lập Nginx gateway, SSL và security headers.
- `Commit 2: Tich hop Prometheus + Grafana giam sat Container, Nginx va MySQL`: Tích hợp bộ công cụ giám sát chỉ số và dashboard tự động.
- `Commit 3: Trien khai he thong log tap trung Loki + Promtail va truy van LogQL`: Hoàn thiện hệ thống gom log tập trung và 3 câu truy vấn LogQL chuẩn hóa.
- `Commit 4: Gia co bao mat he thong (Hardening) va hoan thien website quan ly kho`: Triển khai phân vùng mạng, non-root user, resource limits và hoàn thiện tài liệu.