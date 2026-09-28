<?php
/**
 * Quan ly Nha cung cap (Suppliers)
 */
require_once __DIR__ . '/helpers/auth.php';
require_login();

$pageTitle = 'Nhà Cung Cấp Hàng Hóa';

$search = trim($_GET['search'] ?? '');
$sql = "SELECT * FROM suppliers WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$suppliers = $stmt->fetchAll();

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/sidebar.php';
require_once __DIR__ . '/views/navbar.php';
?>

<div class="page-content">
    <div class="filter-bar">
        <form method="GET" action="suppliers.php" style="display: flex; gap: 12px; flex: 1; max-width: 400px;">
            <div class="search-input-group" style="width: 100%;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" class="form-control" placeholder="Tìm theo tên, SĐT, email..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit" class="btn btn-secondary">Tìm</button>
            <?php if (!empty($search)): ?>
                <a href="suppliers.php" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </form>

        <button class="btn btn-primary" onclick="openModal('addSupplierModal')">
            <i class="fa-solid fa-plus"></i> Thêm Nhà Cung Cấp
        </button>
    </div>

    <div class="glass-panel" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Tên Nhà Cung Cấp</th>
                        <th>Số Điện Thoại</th>
                        <th>Email Liên Hệ</th>
                        <th>Địa Chỉ Trụ Sở</th>
                        <th style="text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($suppliers)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">Không tìm thấy đối tác cung cấp nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($suppliers as $s): ?>
                            <tr>
                                <td><strong>#<?= $s['id'] ?></strong></td>
                                <td>
                                    <strong style="color: #818cf8;"><?= htmlspecialchars($s['name']) ?></strong>
                                </td>
                                <td>
                                    <i class="fa-solid fa-phone" style="font-size: 11px; color: var(--accent); margin-right: 5px;"></i>
                                    <?= htmlspecialchars($s['phone'] ?? 'Chưa cập nhật') ?>
                                </td>
                                <td>
                                    <i class="fa-regular fa-envelope" style="font-size: 11px; color: var(--info); margin-right: 5px;"></i>
                                    <?= htmlspecialchars($s['email'] ?? 'Chưa cập nhật') ?>
                                </td>
                                <td style="color: var(--text-secondary); max-width: 250px;">
                                    <?= htmlspecialchars($s['address'] ?? 'Chưa có địa chỉ') ?>
                                </td>
                                <td style="text-align: right;">
                                    <button class="btn btn-secondary btn-sm" onclick='editSupplier(<?= json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <?php if (is_admin()): ?>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDeleteSupplier(<?= $s['id'] ?>, '<?= htmlspecialchars($s['name'], ENT_QUOTES) ?>')" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Them Nha Cung Cap -->
<div id="addSupplierModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-truck-field" style="color: var(--primary);"></i> Thêm Nhà Cung Cấp Mới</h3>
            <button class="modal-close" onclick="closeModal('addSupplierModal')">&times;</button>
        </div>
        <form action="api/suppliers.php" method="POST">
            <input type="hidden" name="action" value="create">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tên Công Ty / Doanh Nghiệp (*)</label>
                    <input type="text" name="name" class="form-control" placeholder="VD: Tập đoàn Công nghệ SamTech" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Số Điện Thoại</label>
                        <input type="text" name="phone" class="form-control" placeholder="024xxxxxx hoặc 09xxxxxx">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Liên Hệ</label>
                        <input type="email" name="email" class="form-control" placeholder="contact@company.com">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Địa Chỉ Trụ Sở</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Số nhà, đường, quận/huyện, tỉnh/thành phố..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addSupplierModal')">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Lưu Nhà Cung Cấp</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sua Nha Cung Cap -->
<div id="editSupplierModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-pen" style="color: var(--primary);"></i> Cập Nhật Nhà Cung Cấp</h3>
            <button class="modal-close" onclick="closeModal('editSupplierModal')">&times;</button>
        </div>
        <form action="api/suppliers.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_sup_id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tên Doanh Nghiệp (*)</label>
                    <input type="text" name="name" id="edit_sup_name" class="form-control" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Số Điện Thoại</label>
                        <input type="text" name="phone" id="edit_sup_phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Liên Hệ</label>
                        <input type="email" name="email" id="edit_sup_email" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Địa Chỉ Trụ Sở</label>
                    <textarea name="address" id="edit_sup_address" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editSupplierModal')">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Cập Nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSupplier(s) {
    document.getElementById('edit_sup_id').value = s.id;
    document.getElementById('edit_sup_name').value = s.name;
    document.getElementById('edit_sup_phone').value = s.phone || '';
    document.getElementById('edit_sup_email').value = s.email || '';
    document.getElementById('edit_sup_address').value = s.address || '';
    openModal('editSupplierModal');
}

function confirmDeleteSupplier(id, name) {
    if (confirm(`Bạn có chắc muốn xóa nhà cung cấp "${name}"?`)) {
        window.location.href = `api/suppliers.php?action=delete&id=${id}`;
    }
}
</script>

<?php require_once __DIR__ . '/views/footer.php'; ?>
