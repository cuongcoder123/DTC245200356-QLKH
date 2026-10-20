# DỰ ÁN BÁO CÁO BÀI TẬP LỚN - ĐỊNH DẠNG LATEX

**Học phần:** Triển khai & Quản trị Hệ thống / DevOps  
**Đề tài:** Xây dựng và Triển khai Hệ thống Quản lý Kho hàng (Inventory Management System)  
**Sinh viên thực hiện:** Thân Phú Cường -- MSSV: DTC245200356 -- Lớp: CNTTK23L  
**Giảng viên hướng dẫn:** TS. Vũ Văn Diện  
**Đơn vị:** Khoa Công nghệ Thông tin -- Trường Đại học Công nghệ Thông tin và Truyền thông (ICTU)

---

## 1. Cấu trúc thư mục

```
latex/
├── BAO_CAO_BAI_TAP_LON_HOAN_CHINH.tex  # File mã nguồn LaTeX hoàn chỉnh (Master)
├── main.tex                            # File chuẩn điểm vào cho Overleaf / VS Code
├── BAO_CAO_BAI_TAP_LON_HOAN_CHINH.pdf  # File PDF đã biên dịch hoàn chỉnh (30 trang)
├── compile.bat                         # File chạy biên dịch 1-click trên Windows (CMD)
├── compile.ps1                         # Script biên dịch tự động bằng PowerShell
├── images/                             # Thư mục hình ảnh minh họa & logo trường ICTU
│   ├── ictu_logo.png
│   ├── 01_dang_nhap.png
│   ├── 02_tong_quan_dashboard.png
│   ├── 03_quan_ly_hang_hoa.png
│   ├── 04_danh_muc_san_pham.png
│   ├── 05_nha_cung_cap.png
│   ├── 06_giao_dich_nhap_xuat.png
│   ├── 07_bao_cao_thong_ke.png
│   ├── 08_trung_tam_giam_sat_logql.png
│   ├── 09_grafana_dashboard.png
│   ├── 10_phpmyadmin.png
│   └── 11_prometheus_targets.png
└── BAO_CAO_BAI_TAP_LON_HOAN_CHINH.html # File gốc HTML đối sánh giao diện
```

---

## 2. Các tính năng định dạng học thuật nổi bật

1. **Trang bìa chuẩn ICTU:** Khung viền kép đồ họa TikZ, logo trường ICTU, ngôi sao phân cách, thông tin sinh viên và giảng viên đóng khung chuẩn xác.
2. **Hỗ trợ Tiếng Việt chuẩn:** Sử dụng bảng mã UTF-8 với `babel-vietnamese`, font chữ `mathptmx` (Times New Roman tiêu chuẩn văn bản học thuật Việt Nam).
3. **Mục lục & Danh mục tự động:** Tự động tạo và cập nhật Mục lục (`\tableofcontents`), Danh mục hình vẽ (`\listoffigures`) và Danh mục bảng biểu (`\listoftables`).
4. **Header & Footer chuyên nghiệp:** Sử dụng `fancyhdr` với tiêu đề trên running head và số trang đánh số chuẩn xác (La Mã cho phần đầu, số Ả Rập cho các chương nội dung).
5. **Đầy đủ 9 Chương & 10 Test Cases:** Toàn bộ bảng ma trận kiểm thử thực nghiệm với badge trạng thái PASS màu xanh, các khối mã nguồn LogQL, Nginx, Docker lệnh chạy rõ nét.

---

## 3. Cách biên dịch file LaTeX

### Cách 1: Chạy file script tự động (Khuyên dùng)
- Nhấp đúp chuột vào file `compile.bat` trên Windows, hoặc:
- Mở PowerShell trong thư mục `latex` và gõ:
  ```powershell
  .\compile.ps1
  ```

### Cách 2: Sử dụng dòng lệnh Terminal
Chạy 2 lần `pdflatex` để cập nhật đầy đủ liên kết mục lục:
```bash
pdflatex -interaction=nonstopmode BAO_CAO_BAI_TAP_LON_HOAN_CHINH.tex
pdflatex -interaction=nonstopmode BAO_CAO_BAI_TAP_LON_HOAN_CHINH.tex
```

### Cách 3: Biên dịch trên Overleaf
1. Nén toàn bộ thư mục `latex/` thành file `.zip`.
2. Truy cập [Overleaf](https://www.overleaf.com), chọn **New Project** -> **Upload Project**.
3. Chọn file `main.tex` làm **Main document**, thiết lập Compiler là **pdfLaTeX** và nhấn **Recompile**.
