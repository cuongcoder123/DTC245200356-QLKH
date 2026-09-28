<?php
/**
 * Navbar Template
 */
?>
<header class="app-navbar">
    <div class="nav-left">
        <h1 class="page-title"><?= htmlspecialchars($pageTitle ?? 'Tổng quan') ?></h1>
        <div class="system-status-pills">
            <span class="status-pill" title="Mã hóa SSL/TLS Reverse Proxy">
                <span class="status-dot"></span> HTTPS (SSL)
            </span>
            <span class="status-pill" title="MySQL 8.0 Container Connected">
                <i class="fa-solid fa-database" style="color: #38bdf8; font-size: 10px;"></i> MySQL 8.0
            </span>
            <span class="status-pill" title="Loki Log Collection Active">
                <i class="fa-solid fa-file-waveform" style="color: #a855f7; font-size: 10px;"></i> Loki/Promtail
            </span>
        </div>
    </div>

    <div class="nav-right">
        <!-- Quick Action Buttons -->
        <button class="btn btn-success btn-sm" onclick="openModal('quickImportModal')">
            <i class="fa-solid fa-arrow-down"></i> Nhập kho
        </button>
        <button class="btn btn-primary btn-sm" onclick="openModal('quickExportModal')">
            <i class="fa-solid fa-arrow-up"></i> Xuất kho
        </button>

        <!-- Theme Toggle -->
        <button class="btn-icon" onclick="toggleTheme()" title="Chuyển chế độ Sáng / Tối">
            <i class="fa-solid fa-moon"></i>
        </button>

        <!-- Logs Direct Quick View -->
        <a href="system.php" class="btn-icon" title="Kiểm tra hệ thống log">
            <i class="fa-solid fa-terminal"></i>
        </a>
    </div>
</header>
