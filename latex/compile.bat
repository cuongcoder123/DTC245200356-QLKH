@echo off
chcp 65001 >nul
echo ========================================================
echo   BIÊN DỊCH BÁO CÁO BÀI TẬP LỚN LATEX (ICTU)
echo ========================================================
echo.

echo [1/2] Đang biên dịch lượt 1 (khởi tạo mục lục & nhãn)...
pdflatex -interaction=nonstopmode BAO_CAO_BAI_TAP_LON_HOAN_CHINH.tex

echo.
echo [2/2] Đang biên dịch lượt 2 (cập nhật liên kết chéo & số trang)...
pdflatex -interaction=nonstopmode BAO_CAO_BAI_TAP_LON_HOAN_CHINH.tex

echo.
if exist BAO_CAO_BAI_TAP_LON_HOAN_CHINH.pdf (
    echo [THÀNH CÔNG] Đã tạo file: BAO_CAO_BAI_TAP_LON_HOAN_CHINH.pdf
) else (
    echo [LỖI] Quá trình biên dịch gặp sự cố. Vui lòng kiểm tra file log!
)
echo ========================================================
pause
