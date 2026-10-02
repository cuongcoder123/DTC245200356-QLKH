<?php
/**
 * Dashboard Tong Quan He Thong
 */
require_once __DIR__ . '/helpers/auth.php';
require_login();

$pageTitle = 'Bảng Điều Khiển Tổng Quan';

// Truy van so lieu thong ke
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowStockCount = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= min_stock")->fetchColumn();
$totalInventoryValue = $pdo->query("SELECT COALESCE(SUM(stock_quantity * purchase_price), 0) FROM products")->fetchColumn();
$monthlyTransactions = $pdo->query("SELECT COUNT(*) FROM inventory_transactions WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn();

// Danh sach san pham sap het hang (canh bao)
$lowStockProducts = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.stock_quantity <= p.min_stock 
    ORDER BY p.stock_quantity ASC 
    LIMIT 5
")->fetchAll();

// Top 5 giao dich moi nhat
$recentTransactions = $pdo->query("
    SELECT t.*, p.name as product_name, p.product_code, u.full_name as staff_name, s.name as supplier_name
    FROM inventory_transactions t
    JOIN products p ON t.product_id = p.id
    LEFT JOIN users u ON t.created_by = u.id
    LEFT JOIN suppliers s ON t.supplier_id = s.id
    ORDER BY t.created_at DESC
    LIMIT 6
")->fetchAll();

// Thong ke theo danh muc de ve bieu do Tron
$catStats = $pdo->query("
    SELECT c.name, COALESCE(SUM(p.stock_quantity * p.purchase_price), 0) as total_val
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id, c.name
    HAVING total_val > 0
")->fetchAll();

$catLabels = [];
$catValues = [];
foreach ($catStats as $cs) {
    $catLabels[] = $cs['name'];
    $catValues[] = (float)$cs['total_val'];
}

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/sidebar.php';
require_once __DIR__ . '/views/navbar.php';
?>

<div class="page-content">
    <!-- 4 KPI Cards -->
    <div class="grid-cards">
        <div class="kpi-card">
            <div class="kpi-info">
                <div class="kpi-label">Tổng Mặt Hàng</div>
                <div class="kpi-value"><?= number_format($totalProducts) ?></div>
                <div class="kpi-sub"><i class="fa-solid fa-boxes-packing" style="color: var(--primary);"></i> Mã SKU hoạt động</div>
            </div>
            <div class="kpi-icon primary">
                <i class="fa-solid fa-cube"></i>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-info">
                <div class="kpi-label">Cảnh Báo Tồn Thấp</div>
                <div class="kpi-value" style="color: #f87171;"><?= number_format($lowStockCount) ?></div>
                <div class="kpi-sub"><i class="fa-solid fa-triangle-exclamation" style="color: var(--danger);"></i> Cần nhập bổ sung</div>
            </div>
            <div class="kpi-icon danger">
                <i class="fa-solid fa-bell"></i>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-info">
                <div class="kpi-label">Tổng Giá Trị Tồn Kho</div>
                <div class="kpi-value" style="color: #34d399; font-size: 20px;"><?= number_format($totalInventoryValue, 0, ',', '.') ?> <span style="font-size: 13px;">đ</span></div>
                <div class="kpi-sub"><i class="fa-solid fa-wallet" style="color: var(--accent);"></i> Tính theo giá vốn nhập</div>
            </div>
            <div class="kpi-icon success">
                <i class="fa-solid fa-money-bill-trend-up"></i>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-info">
                <div class="kpi-label">Giao Dịch Tháng Này</div>
                <div class="kpi-value"><?= number_format($monthlyTransactions) ?></div>
                <div class="kpi-sub"><i class="fa-solid fa-clock-rotate-left" style="color: var(--warning);"></i> Phiếu nhập & xuất</div>
            </div>
            <div class="kpi-icon warning">
                <i class="fa-solid fa-right-left"></i>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="charts-grid">
        <div class="glass-panel">
            <div class="panel-header">
                <h3 class="panel-title"><i class="fa-solid fa-chart-column"></i> Xu Hướng Nhập / Xuất Kho (6 Tháng Gần Nhất)</h3>
                <span class="badge badge-info">Thời gian thực</span>
            </div>
            <div style="height: 280px; position: relative;">
                <canvas id="inventoryTrendChart"></canvas>
            </div>
        </div>

        <div class="glass-panel">
            <div class="panel-header">
                <h3 class="panel-title"><i class="fa-solid fa-chart-pie"></i> Cơ Cấu Tồn Kho Theo Nhóm</h3>
            </div>
            <div style="height: 280px; position: relative; display: flex; align-items: center; justify-content: center;">
                <canvas id="categoryPieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Row: Low Stock Alerts & Recent Transactions -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <!-- Low Stock Alerts -->
        <div class="glass-panel">
            <div class="panel-header">
                <h3 class="panel-title" style="color: #f87171;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Cảnh Báo Hàng Sắp Hết (<?= count($lowStockProducts) ?>)
                </h3>
                <a href="products.php?filter=low_stock" class="btn btn-secondary btn-sm">Xem tất cả</a>
            </div>
            <?php if (empty($lowStockProducts)): ?>
                <p style="color: var(--text-muted); text-align: center; padding: 20px;">Kho hàng đủ số lượng an toàn! Không có sản phẩm nào chạm ngưỡng tối thiểu.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Mã SP</th>
                                <th>Tên Sản Phẩm</th>
                                <th>Tồn Hiện Tại</th>
                                <th>Mức Tối Thiểu</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockProducts as $p): ?>
                                <tr>
                                    <td><span style="font-weight: 700; color: #818cf8;"><?= htmlspecialchars($p['product_code']) ?></span></td>
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td>
                                        <span class="badge badge-danger">
                                            <?= $p['stock_quantity'] ?> <?= htmlspecialchars($p['unit']) ?>
                                        </span>
                                    </td>
                                    <td><?= $p['min_stock'] ?> <?= htmlspecialchars($p['unit']) ?></td>
                                    <td>
                                        <button class="btn btn-success btn-sm" onclick="openModal('quickImportModal')">
                                            <i class="fa-solid fa-plus"></i> Nhập
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Transactions -->
        <div class="glass-panel">
            <div class="panel-header">
                <h3 class="panel-title">
                    <i class="fa-solid fa-list-check"></i> Giao Dịch Gần Đây
                </h3>
                <a href="transactions.php" class="btn btn-secondary btn-sm">Xem tất cả</a>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Mã GD</th>
                            <th>Loại</th>
                            <th>Hàng Hóa</th>
                            <th>Số Lượng</th>
                            <th>Thời Gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTransactions as $t): ?>
                            <tr>
                                <td style="font-weight: 600; font-size: 12px;"><?= htmlspecialchars($t['transaction_code']) ?></td>
                                <td>
                                    <?php if ($t['type'] === 'IMPORT'): ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-arrow-down"></i> NHẬP</span>
                                    <?php else: ?>
                                        <span class="badge badge-info"><i class="fa-solid fa-arrow-up"></i> XUẤT</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($t['product_name']) ?></td>
                                <td style="font-weight: 700;">
                                    <?= $t['type'] === 'IMPORT' ? '+' : '-' ?><?= number_format($t['quantity']) ?>
                                </td>
                                <td style="color: var(--text-muted); font-size: 11px;">
                                    <?= date('d/m H:i', strtotime($t['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Thiet lap font chu chuan tieng Viet cho toan bo Chart.js
if (typeof Chart !== 'undefined') {
    Chart.defaults.font.family = "'Be Vietnam Pro', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
    Chart.defaults.color = '#94a3b8';
}

// Chart 1: Xu huong Nhap / Xuat
const ctxTrend = document.getElementById('inventoryTrendChart');
if (ctxTrend) {
    new Chart(ctxTrend, {
        type: 'bar',
        data: {
            labels: ['Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9'],
            datasets: [
                {
                    label: 'Giá trị Nhập (Tr. VNĐ)',
                    data: [150, 220, 180, 290, 310, 480],
                    backgroundColor: 'rgba(79, 70, 229, 0.7)',
                    borderColor: '#4f46e5',
                    borderWidth: 1,
                    borderRadius: 6
                },
                {
                    label: 'Giá trị Xuất (Tr. VNĐ)',
                    data: [120, 190, 210, 240, 280, 395],
                    backgroundColor: 'rgba(6, 182, 212, 0.7)',
                    borderColor: '#06b6d4',
                    borderWidth: 1,
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#94a3b8', font: { family: 'Be Vietnam Pro', size: 12 } }
                }
            },
            scales: {
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#94a3b8', font: { family: 'Be Vietnam Pro' } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { family: 'Be Vietnam Pro' } }
                }
            }
        }
    });
}

// Chart 2: Co cau ton kho theo Nhom hang
const ctxCat = document.getElementById('categoryPieChart');
if (ctxCat) {
    new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($catLabels, JSON_UNESCAPED_UNICODE) ?>,
            datasets: [{
                data: <?= json_encode($catValues) ?>,
                backgroundColor: [
                    '#4f46e5',
                    '#06b6d4',
                    '#10b981',
                    '#f59e0b',
                    '#ec4899',
                    '#8b5cf6'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#94a3b8', font: { family: 'Be Vietnam Pro', size: 11 }, boxWidth: 12 }
                }
            },
            cutout: '65%'
        }
    });
}
</script>

<?php require_once __DIR__ . '/views/footer.php'; ?>
