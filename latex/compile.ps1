# Script biên dịch LaTeX tự động trên PowerShell
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8
Write-Host "========================================================" -ForegroundColor Cyan
Write-Host "   BIÊN DỊCH BÁO CÁO BÀI TẬP LỚN LATEX (ICTU)" -ForegroundColor Yellow
Write-Host "========================================================" -ForegroundColor Cyan

$texFile = "BAO_CAO_BAI_TAP_LON_HOAN_CHINH.tex"
$pdfFile = "BAO_CAO_BAI_TAP_LON_HOAN_CHINH.pdf"

Write-Host "`n[1/2] Đang biên dịch lượt 1..." -ForegroundColor Green
pdflatex -interaction=nonstopmode $texFile

Write-Host "`n[2/2] Đang biên dịch lượt 2 (cập nhật Mục lục & Hình vẽ)..." -ForegroundColor Green
pdflatex -interaction=nonstopmode $texFile

if (Test-Path $pdfFile) {
    Write-Host "`n[THÀNH CÔNG] File PDF đã sẵn sàng: $pdfFile" -ForegroundColor Cyan
} else {
    Write-Host "`n[LỖI] Không tạo được file PDF. Vui lòng kiểm tra log!" -ForegroundColor Red
}
