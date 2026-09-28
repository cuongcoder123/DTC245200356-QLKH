<?php
/**
 * Quan ly Giao dich Nhap / Xuat Kho (Transactions)
 */
require_once __DIR__ . '/helpers/auth.php';
require_login();

$pageTitle = 'Giao Dịch Nhập / Xuất Kho';

$typeFilter = $_GET['type'] ?? '';
$search = trim($_GET['search'] ?? '');
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

$sql = "
    SELECT t.*, p.product_code, p.name as product_name, p.unit, 
           s.name as supplier_name, u.full_name as user_name
    FROM inventory_transactions t
    JOIN products p ON t.product_id = p.id
    LEFT JOIN suppliers s ON t.supplier_id = s.id
    LEFT JOIN users u ON t.created_by = u.id
    WHERE 1=1
";
$params = [];

if (!empty($typeFilter)) {
    $sql .= " AND t.type = ?";
    $params[] = $typeFilter;
}

if (!empty($search)) {
    $sql .= " AND (t.transaction_code LIKE ? OR p.name LIKE ? OR p.product_code LIKE ? OR t.note LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($fromDate)) {
    $sql .= " AND DATE(t.created_at) >= ?";
    $params[] = $fromDate;
}

if (!empty($toDate)) {
    $sql .= " AND DATE(t.created_at) <= ?";
    $params[] = $toDate;
}

$sql .= " ORDER BY t.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Tinh tong gia tri giao dich dang hien thi
$totalAmount = 0;
$totalQty = 0;
foreach ($transactions as $t) {
    $totalAmount += (float)$t['total_amount'];
    $totalQty += (int)$t['quantity'];
}

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/sidebar.php';
require_once __DIR__ . '/views/navbar.php';
?>

<div class="page-content">
    <!-- Filter and Action Bar -->
    <div class="filter-bar">
        <form method="GET" action="transactions.php" style="display: flex; gap: 12px; flex-wrap: wrap; flex: 1;">
            <div class="search-input-group" style="min-width: 200px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" class="form-control" placeholder="Mã phiếu, sản phẩm, ghi chú..." value="<?= htmlspecialchars($search) ?>">
            </div>

            <select name="type" class="form-control" style="width: 150px;" onchange="this.form.submit()">
                <option value="">-- Tất cả loại GD --</option>
                <option value="IMPORT" <?= $typeFilter === 'IMPORT' ? 'selected' : '' ?>>Chỉ Nhập Kho</option>
                <option value="EXPORT" <?= $typeFilter === 'EXPORT' ? 'selected' : '' ?>>Chỉ Xuất Kho</option>
            </select>

            <input type="date" name="from_date" class="form-control" style="width: 150px;" value="<?= htmlspecialchars($fromDate) ?>" title="Từ ngày">
            <input type="date" name="to_date" class="form-control" style="width: 150px;" value="<?= htmlspecialchars($toDate) ?>" title="Đến ngày">

            <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-filter"></i> Lọc</button>
            <?php if (!empty($search) || !empty($typeFilter) || !empty($fromDate) || !empty($toDate)): ?>
                <a href="transactions.php" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Bỏ lọc</a>
            <?php endif; ?>
        </form>

        <div style="display: flex; gap: 10px;">
            <button class="btn btn-secondary" onclick="exportTableToCSV('transTable', 'giao_dich_kho.csv')">
                <i class="fa-solid fa-file-csv"></i> Xuất CSV
            </button>
            <button class="btn btn-success" onclick="openModal('quickImportModal')">
                <i class="fa-solid fa-arrow-down"></i> Lập Phiếu Nhập
            </button>
            <button class="btn btn-primary" onclick="openModal('quickExportModal')">
                <i class="fa-solid fa-arrow-up"></i> Lập Phiếu Xuất
            </button>
        </div>
    </div>

    <!-- Summary Box -->
    <div style="display: flex; gap: 20px; margin-bottom: 20px; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px 24px;">
        <div>
            <div style="font-size: 12px; color: var(--text-muted);">Tổng số giao dịch:</div>
            <div style="font-size: 18px; font-weight: 800; color: var(--text-primary);"><?= count($transactions) ?> phiếu</div>
        </div>
        <div style="border-left: 1px solid var(--border-color); padding-left: 20px;">
            <div style="font-size: 12px; color: var(--text-muted);">Tổng sản phẩm luân chuyển:</div>
            <div style="font-size: 18px; font-weight: 800; color: #818cf8;"><?= number_format($totalQty) ?> đơn vị</div>
        </div>
        <div style="border-left: 1px solid var(--border-color); padding-left: 20px;">
            <div style="font-size: 12px; color: var(--text-muted);">Tổng giá trị tiền giao dịch:</div>
            <div style="font-size: 18px; font-weight: 800; color: #34d399;"><?= number_format($totalAmount, 0, ',', '.') ?> VNĐ</div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="glass-panel" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="custom-table" id="transTable">
                <thead>
                    <tr>
                        <th>Mã Phiếu</th>
                        <th>Loại Phiếu</th>
                        <th>Sản Phẩm</th>
                        <th>Số Lượng</th>
                        <th>Đơn Giá</th>
                        <th>Thành Tiền</th>
                        <th>Đối Tác / Ghi Chú</th>
                        <th>Người Lập</th>
                        <th>Thời Gian</th>
                        <th class="no-export" style="text-align: right;">In / Xem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                Không có bản ghi giao dịch nào thỏa mãn điều kiện lọc.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td>
                                    <strong style="color: #818cf8; font-family: monospace; font-size: 12px;"><?= htmlspecialchars($t['transaction_code']) ?></strong>
                                </td>
                                <td>
                                    <?php if ($t['type'] === 'IMPORT'): ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-arrow-down"></i> NHẬP KHO</span>
                                    <?php else: ?>
                                        <span class="badge badge-info"><i class="fa-solid fa-arrow-up"></i> XUẤT KHO</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><strong><?= htmlspecialchars($t['product_name']) ?></strong></div>
                                    <span style="font-size: 11px; color: var(--text-muted);">Mã: <?= htmlspecialchars($t['product_code']) ?></span>
                                </td>
                                <td>
                                    <strong style="color: <?= $t['type'] === 'IMPORT' ? '#34d399' : '#60a5fa' ?>;">
                                        <?= $t['type'] === 'IMPORT' ? '+' : '-' ?><?= number_format($t['quantity']) ?> <?= htmlspecialchars($t['unit']) ?>
                                    </strong>
                                </td>
                                <td><?= number_format($t['unit_price'], 0, ',', '.') ?> đ</td>
                                <td style="font-weight: 700; color: #34d399;">
                                    <?= number_format($t['total_amount'], 0, ',', '.') ?> đ
                                </td>
                                <td style="max-width: 200px;">
                                    <?php if (!empty($t['supplier_name'])): ?>
                                        <div><i class="fa-solid fa-truck" style="font-size: 10px; color: var(--primary);"></i> <?= htmlspecialchars($t['supplier_name']) ?></div>
                                    <?php endif; ?>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($t['note'] ?? '') ?></div>
                                </td>
                                <td>
                                    <span style="font-size: 12px;"><?= htmlspecialchars($t['user_name'] ?? 'Hệ thống') ?></span>
                                </td>
                                <td style="color: var(--text-muted); font-size: 12px;">
                                    <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                                </td>
                                <td class="no-export" style="text-align: right;">
                                    <button class="btn btn-secondary btn-sm" onclick='viewReceipt(<?= json_encode($t, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' title="Xem & In phiếu">
                                        <i class="fa-solid fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Xem & In Phieu Kho -->
<div id="receiptModal" class="modal-overlay">
    <div class="modal-container" style="max-width: 650px;">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-file-invoice"></i> Chi Tiết Phiếu Kho</h3>
            <button class="modal-close" onclick="closeModal('receiptModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div id="printableArea" style="background: #fff; color: #1e293b; padding: 30px; border-radius: 8px; font-family: 'Plus Jakarta Sans', sans-serif;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px;">
                    <div>
                        <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0;">KHO HÀNG THÔNG MINH - ĐỀ 05</h2>
                        <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;">Đồ án DevOps / Quản trị hệ thống - DTC245200356</p>
                    </div>
                    <div style="text-align: right;">
                        <h3 id="rc_title" style="font-size: 16px; font-weight: 800; color: #4338ca; margin: 0;">PHIẾU NHẬP KHO</h3>
                        <p id="rc_code" style="font-family: monospace; font-weight: 700; color: #334155; margin: 2px 0 0 0;"></p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 13px; margin-bottom: 20px;">
                    <div>
                        <p><strong>Ngày lập phiếu:</strong> <span id="rc_date"></span></p>
                        <p><strong>Người lập:</strong> <span id="rc_user"></span></p>
                    </div>
                    <div>
                        <p><strong>Đối tác / Nhà CC:</strong> <span id="rc_partner"></span></p>
                        <p><strong>Ghi chú:</strong> <span id="rc_note"></span></p>
                    </div>
                </div>

                <table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 20px;">
                    <thead>
                        <tr style="background: #f1f5f9; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                            <th style="padding: 8px; text-align: left;">Mã Hàng</th>
                            <th style="padding: 8px; text-align: left;">Tên Mặt Hàng</th>
                            <th style="padding: 8px; text-align: center;">ĐVT</th>
                            <th style="padding: 8px; text-align: right;">SL</th>
                            <th style="padding: 8px; text-align: right;">Đơn Giá</th>
                            <th style="padding: 8px; text-align: right;">Thành Tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td id="rc_pcode" style="padding: 10px 8px; font-weight: 600;"></td>
                            <td id="rc_pname" style="padding: 10px 8px;"></td>
                            <td id="rc_unit" style="padding: 10px 8px; text-align: center;"></td>
                            <td id="rc_qty" style="padding: 10px 8px; text-align: right; font-weight: 700;"></td>
                            <td id="rc_price" style="padding: 10px 8px; text-align: right;"></td>
                            <td id="rc_total" style="padding: 10px 8px; text-align: right; font-weight: 700; color: #047857;"></td>
                        </tr>
                    </tbody>
                </table>

                <div style="display: flex; justify-content: space-around; margin-top: 40px; text-align: center; font-size: 13px;">
                    <div>
                        <p><strong>Người Nhận Hàng</strong></p>
                        <p style="color: #94a3b8; font-size: 11px;">(Ký, ghi rõ họ tên)</p>
                    </div>
                    <div>
                        <p><strong>Thủ Kho Quản Lý</strong></p>
                        <p style="color: #94a3b8; font-size: 11px;">(Ký, ghi rõ họ tên)</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('receiptModal')">Đóng</button>
            <button type="button" class="btn btn-primary" onclick="printReceipt('receiptModal')"><i class="fa-solid fa-print"></i> In Phiếu Ngay</button>
        </div>
    </div>
</div>

<script>
function viewReceipt(t) {
    document.getElementById('rc_title').innerText = t.type === 'IMPORT' ? 'PHIẾU NHẬP KHO' : 'PHIẾU XUẤT KHO';
    document.getElementById('rc_title').style.color = t.type === 'IMPORT' ? '#047857' : '#2563eb';
    document.getElementById('rc_code').innerText = t.transaction_code;
    document.getElementById('rc_date').innerText = t.created_at;
    document.getElementById('rc_user').innerText = t.user_name || 'Quản trị viên';
    document.getElementById('rc_partner').innerText = t.supplier_name || 'Khách hàng / Nội bộ';
    document.getElementById('rc_note').innerText = t.note || 'Không có';
    document.getElementById('rc_pcode').innerText = t.product_code;
    document.getElementById('rc_pname').innerText = t.product_name;
    document.getElementById('rc_unit').innerText = t.unit;
    document.getElementById('rc_qty').innerText = Number(t.quantity).toLocaleString();
    document.getElementById('rc_price').innerText = Number(t.unit_price).toLocaleString() + ' đ';
    document.getElementById('rc_total').innerText = Number(t.total_amount).toLocaleString() + ' đ';
    openModal('receiptModal');
}
</script>

<?php require_once __DIR__ . '/views/footer.php'; ?>
