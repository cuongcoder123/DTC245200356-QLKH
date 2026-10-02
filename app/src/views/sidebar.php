<?php
/**
 * Sidebar Template
 */
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="app-sidebar">
    <div class="sidebar-brand">
        <div class="logo-icon">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div class="brand-text">
            <span class="title">SMART INVENTORY</span>
            <span class="subtitle">DTC245200356 • Đề 05</span>
        </div>
    </div>

    <div class="sidebar-menu">
        <div class="menu-label">Nghiệp Vụ Kho</div>
        <a href="index.php" class="menu-item <?= $currentPage == 'index.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Tổng quan (Dashboard)</span>
        </a>
        <a href="products.php" class="menu-item <?= $currentPage == 'products.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-box-open"></i>
            <span>Quản lý Hàng hóa</span>
        </a>
        <a href="categories.php" class="menu-item <?= $currentPage == 'categories.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-tags"></i>
            <span>Danh mục Sản phẩm</span>
        </a>
        <a href="suppliers.php" class="menu-item <?= $currentPage == 'suppliers.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-truck-field"></i>
            <span>Nhà cung cấp</span>
        </a>
        <a href="transactions.php" class="menu-item <?= $currentPage == 'transactions.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-arrow-right-arrow-left"></i>
            <span>Giao dịch Nhập / Xuất</span>
        </a>
        <a href="reports.php" class="menu-item <?= $currentPage == 'reports.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            <span>Báo cáo & Thống kê</span>
        </a>

        <div class="menu-label">Hạ Tầng & DevOps</div>
        <a href="system.php" class="menu-item <?= $currentPage == 'system.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-server"></i>
            <span>Giám Sát & LogQL</span>
        </a>
        <a href="/grafana/" target="_blank" class="menu-item">
            <i class="fa-solid fa-chart-line" style="color: #f97316;"></i>
            <span>Grafana Dashboard <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 10px; margin-left: 4px;"></i></span>
        </a>
        <a href="/pma/" target="_blank" class="menu-item">
            <i class="fa-solid fa-database" style="color: #38bdf8;"></i>
            <span>phpMyAdmin <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 10px; margin-left: 4px;"></i></span>
        </a>
        <a href="/prometheus/targets" target="_blank" class="menu-item">
            <i class="fa-solid fa-fire-flame-curved" style="color: #ef4444;"></i>
            <span>Prometheus Targets <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 10px; margin-left: 4px;"></i></span>
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-avatar">
                <?= strtoupper(substr($currentUser['username'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="user-info">
                <div class="name"><?= htmlspecialchars($currentUser['full_name'] ?? 'Người dùng') ?></div>
                <span class="role-badge <?= $currentUser['role'] ?? 'staff' ?>">
                    <?= $currentUser['role'] === 'admin' ? 'Admin' : 'Thủ kho' ?>
                </span>
            </div>
            <a href="logout.php" title="Đăng xuất" style="color: var(--danger); font-size: 16px; padding: 6px;">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </div>
</aside>
<main class="app-main">
