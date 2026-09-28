<?php
/**
 * Quan ly Danh muc san pham
 */
require_once __DIR__ . '/helpers/auth.php';
require_login();

$pageTitle = 'Danh Mục Sản Phẩm';

// Lay danh muc kem so luong san pham va tong ton kho
$categories = $pdo->query("
    SELECT c.*, 
           COUNT(p.id) as product_count, 
           COALESCE(SUM(p.stock_quantity), 0) as total_units,
           COALESCE(SUM(p.stock_quantity * p.purchase_price), 0) as total_value
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
    GROUP BY c.id
    ORDER BY c.id ASC
")->fetchAll();

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/sidebar.php';
require_once __DIR__ . '/views/navbar.php';
?>

<div class="page-content">
    <div class="filter-bar">
        <div>
            <h2 style="font-size: 18px; font-weight: 700;">Danh Sách Nhóm Hàng Hóa</h2>
            <p style="font-size: 13px; color: var(--text-muted);">Phân loại các danh mục lưu trữ trong kho</p>
        </div>
        <button class="btn btn-primary" onclick="openModal('addCatModal')">
            <i class="fa-solid fa-plus"></i> Thêm Danh Mục Mới
        </button>
    </div>

    <div class="glass-panel" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="custom-table" id="catTable">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Tên Danh Mục</th>
                        <th>Mô Tả Chi Tiết</th>
                        <th>Số Mặt Hàng</th>
                        <th>Tổng Đơn Vị Tồn</th>
                        <th>Giá Trị Tồn Kho</th>
                        <th style="text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">Chưa có danh mục nào được tạo.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td><strong>#<?= $c['id'] ?></strong></td>
                                <td>
                                    <span style="font-weight: 700; color: #818cf8;"><?= htmlspecialchars($c['name']) ?></span>
                                </td>
                                <td style="color: var(--text-secondary); max-width: 300px;"><?= htmlspecialchars($c['description'] ?? 'Không có mô tả') ?></td>
                                <td>
                                    <span class="badge badge-info"><?= $c['product_count'] ?> mặt hàng</span>
                                </td>
                                <td><?= number_format($c['total_units']) ?></td>
                                <td style="font-weight: 600; color: #34d399;"><?= number_format($c['total_value'], 0, ',', '.') ?> đ</td>
                                <td style="text-align: right;">
                                    <button class="btn btn-secondary btn-sm" onclick='editCategory(<?= json_encode($c, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <?php if (is_admin()): ?>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $c['id'] ?>, '<?= htmlspecialchars($c['name'], ENT_QUOTES) ?>')" title="Xóa">
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

<!-- Modal Them Danh Muc -->
<div id="addCatModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-plus" style="color: var(--primary);"></i> Thêm Danh Mục Mới</h3>
            <button class="modal-close" onclick="closeModal('addCatModal')">&times;</button>
        </div>
        <form action="api/categories.php" method="POST">
            <input type="hidden" name="action" value="create">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tên Danh Mục (*)</label>
                    <input type="text" name="name" class="form-control" placeholder="VD: Thiết bị lưu trữ SAN/NAS" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mô Tả</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Mô tả phạm vi các loại hàng hóa trong danh mục..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addCatModal')">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Lưu Danh Mục</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sua Danh Muc -->
<div id="editCatModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-pen" style="color: var(--primary);"></i> Chỉnh Sửa Danh Mục</h3>
            <button class="modal-close" onclick="closeModal('editCatModal')">&times;</button>
        </div>
        <form action="api/categories.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_cat_id">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Tên Danh Mục (*)</label>
                    <input type="text" name="name" id="edit_cat_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mô Tả</label>
                    <textarea name="description" id="edit_cat_desc" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editCatModal')">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Cập Nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCategory(c) {
    document.getElementById('edit_cat_id').value = c.id;
    document.getElementById('edit_cat_name').value = c.name;
    document.getElementById('edit_cat_desc').value = c.description || '';
    openModal('editCatModal');
}

function confirmDelete(id, name) {
    if (confirm(`Bạn có chắc chắn muốn xóa danh mục "${name}"?`)) {
        window.location.href = `api/categories.php?action=delete&id=${id}`;
    }
}
</script>

<?php require_once __DIR__ . '/views/footer.php'; ?>
