<?php
/**
 * Bao cao & Thong ke Kho Hang
 */
require_once __DIR__ . '/helpers/auth.php';
require_login();

$pageTitle = 'Báo Cáo & Phân Tích Kho Hàng';

// 1. Thong ke tong the
$totalStockValue = $pdo->query("SELECT SUM(stock_quantity * purchase_price) FROM products")->fetchColumn() ?: 0;
$totalExpectedRevenue = $pdo->query("SELECT SUM(stock_quantity * sale_price) FROM products")->fetchColumn() ?: 0;
$expectedProfit = $totalExpectedRevenue - $totalStockValue;

// 2. Thong ke Nhap theo thang
$importStats = $pdo->query("
    SELECT COUNT(*) as total_import, COALESCE(SUM(total_amount), 0) as total_val, COALESCE(SUM(quantity), 0) as total_qty
    FROM inventory_transactions 
    WHERE type = 'IMPORT' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())
")->fetch();

// 3. Thong ke Xuat theo thang
$exportStats = $pdo->query("
    SELECT COUNT(*) as total_export, COALESCE(SUM(total_amount), 0) as total_val, COALESCE(SUM(quantity), 0) as total_qty
    FROM inventory_transactions 
    WHERE type = 'EXPORT' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())
")->fetch();

// 4. Bang chi tiet gia tri ton kho tung san pham
$stockDetails = $pdo->query("
    SELECT p.*, c.name as category_name,
           (p.stock_quantity * p.purchase_price) as total_inventory_cost,
           (p.stock_quantity * p.sale_price) as total_inventory_revenue
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY total_inventory_cost DESC
")->fetchAll();

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/sidebar.php';
require_once __DIR__ . '/views/navbar.php';
?>

<div class="page-content">
    <div class="filter-bar">
        <div>
            <h2 style="font-size: 18px; font-weight: 700;">Báo Cáo Thẩm Định & Luân Chuyển Tồn Kho</h2>
            <p style="font-size: 13px; color: var(--text-muted);">Thống kê tổng giá trị tài sản kho hàng tính đến <?= date('d/m/Y') ?></p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-secondary" onclick="exportTableToCSV('reportTable', 'bao_cao_dinh_gia_kho.csv')">
                <i class="fa-solid fa-file-csv"></i> Tải Báo Cáo CSV
            </button>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fa-solid fa-print"></i> In Báo Cáo
            </button>
        </div>
    </div>

    <!-- KPI Grid -->
    <div class="grid-cards">
        <div class="kpi-card">
            <div class="kpi-info">
                <div class="kpi-label">Vốn Tồn Kho Hiện Tại</div>
                <div class="kpi-value" style="color: #38bdf8; font-size: 20px;"><?= number_format($totalStockValue, 0, ',', '.') ?> đ</div>
                <div class="kpi-sub"><i class="fa-solid fa-coins"></i> Giá vốn thực tế</div>
            </div>
            <div class="kpi-icon primary"><i class="fa-solid fa-vault"></i></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-info">
                <div class="kpi-label">Doanh Thu Dự Kiến</div>
                <div class="kpi-value" style="color: #34d399; font-size: 20px;"><?= number_format($totalExpectedRevenue, 0, ',', '.') ?> đ</div>
                <div class="kpi-sub"><i class="fa-solid fa-arrow-trend-up"></i> Nếu bán hết toàn bộ</div>
            </div>
            <div class="kpi-icon success"><i class="fa-solid fa-chart-line"></i></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-info">
                <div class="kpi-label">Tổng Tiền Nhập Tháng</div>
                <div class="kpi-value" style="font-size: 20px;"><?= number_format($importStats['total_val'], 0, ',', '.') ?> đ</div>
                <div class="kpi-sub"><i class="fa-solid fa-arrow-down" style="color: var(--accent);"></i> <?= number_format($importStats['total_qty']) ?> sản phẩm nhập</div>
            </div>
            <div class="kpi-icon warning"><i class="fa-solid fa-truck-ramp-box"></i></div>
        </div>

        <div class="kpi-card">
            <div class="kpi-info">
                <div class="kpi-label">Tổng Tiền Xuất Tháng</div>
                <div class="kpi-value" style="color: #f59e0b; font-size: 20px;"><?= number_format($exportStats['total_val'], 0, ',', '.') ?> đ</div>
                <div class="kpi-sub"><i class="fa-solid fa-arrow-up" style="color: var(--warning);"></i> <?= number_format($exportStats['total_qty']) ?> sản phẩm xuất</div>
            </div>
            <div class="kpi-icon danger"><i class="fa-solid fa-dolly"></i></div>
        </div>
    </div>

    <!-- Detailed Valuation Table -->
    <div class="glass-panel" style="padding: 0; overflow: hidden;">
        <div class="panel-header" style="padding: 20px 24px 10px 24px; border-bottom: none;">
            <h3 class="panel-title"><i class="fa-solid fa-table-list"></i> Bảng Thẩm Định Giá Trị Từng Mã Mặt Hàng</h3>
        </div>
        <div class="table-responsive">
            <table class="custom-table" id="reportTable">
                <thead>
                    <tr>
                        <th>Mã SP</th>
                        <th>Tên Mặt Hàng</th>
                        <th>Danh Mục</th>
                        <th>SL Tồn</th>
                        <th>ĐVT</th>
                        <th>Giá Vốn Nhập</th>
                        <th>Tổng Giá Vốn Tồn Kho</th>
                        <th>Giá Bán Niêm Yết</th>
                        <th>Tổng Doanh Thu Kỳ Vọng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stockDetails as $s): ?>
                        <tr>
                            <td><strong style="color: #818cf8;"><?= htmlspecialchars($s['product_code']) ?></strong></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><span class="badge badge-info" style="font-size: 10px;"><?= htmlspecialchars($s['category_name'] ?? 'N/A') ?></span></td>
                            <td style="font-weight: 700;"><?= number_format($s['stock_quantity']) ?></td>
                            <td><?= htmlspecialchars($s['unit']) ?></td>
                            <td><?= number_format($s['purchase_price'], 0, ',', '.') ?> đ</td>
                            <td style="font-weight: 700; color: #38bdf8;"><?= number_format($s['total_inventory_cost'], 0, ',', '.') ?> đ</td>
                            <td><?= number_format($s['sale_price'], 0, ',', '.') ?> đ</td>
                            <td style="font-weight: 700; color: #34d399;"><?= number_format($s['total_inventory_revenue'], 0, ',', '.') ?> đ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/footer.php'; ?>
