@echo off
chcp 65001 >nul
echo ========================================================
echo    KHỞI CHẠY BÀI TRÌNH CHIẾU SLIDE ĐỒ ÁN (CYBER TECH)
echo ========================================================
echo.
echo Đang mở slide trên trình duyệt mặc định...
start "" "%~dp0slides.html"
echo.
echo Phím tắt khi thuyết trình:
echo  - Phím Mũi tên Phải / Phím Space: Trang tiếp theo
echo  - Phím Mũi tên Trái / Backspace:  Trang trước
echo  - Phím F: Bật / Tắt Toàn màn hình (Fullscreen)
echo  - Phím O / ESC: Mở danh mục tổng quan 16 Slide
echo  - Nhấp vào ô đồng hồ: Bắt đầu / Tạm dừng đếm giờ
echo  - Nhấp vào ảnh: Phóng to chi tiết (Lightbox)
echo  - Nút In: Xuất sang định dạng PDF (Ctrl + P)
echo.
pause
