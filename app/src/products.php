<?php
/**
 * Quan ly danh sach Hang Hoa (Products)
 */
require_once __DIR__ . '/helpers/auth.php';
require_login();

$pageTitle = 'Quản Lý Hàng Hóa';

$search = trim($_GET['search'] ?? '');
$categoryFilter = $_GET['category'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Xay dung cau truy van co loc
$sql = "
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.product_code LIKE ? OR p.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($categoryFilter)) {
    $sql .= " AND p.category_id = ?";
    $params[] = $categoryFilter;
}

if ($statusFilter === 'low') {
    $sql .= " AND p.stock_quantity > 0 AND p.stock_quantity <= p.min_stock";
} elseif ($statusFilter === 'out') {
    $sql .= " AND p.stock_quantity <= 0";
} elseif ($statusFilter === 'in_stock') {
    $sql .= " AND p.stock_quantity > p.min_stock";
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Lay danh sach categories cho dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/sidebar.php';
require_once __DIR__ . '/views/navbar.php';
?>

<div class="page-content">
    <!-- Filter and Action Bar -->
    <div class="filter-bar">
        <form method="GET" action="products.php" style="display: flex; gap: 12px; flex-wrap: wrap; flex: 1;">
            <div class="search-input-group">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" class="form-control" placeholder="Tìm theo mã SP hoặc tên..." value="<?= htmlspecialchars($search) ?>">
            </div>

            <select name="category" class="form-control" style="width: 200px;" onchange="this.form.submit()">
                <option value="">-- Tất cả danh mục --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="status" class="form-control" style="width: 170px;" onchange="this.form.submit()">
                <option value="">-- Trạng thái tồn --</option>
                <option value="in_stock" <?= $statusFilter == 'in_stock' ? 'selected' : '' ?>>Đủ tồn kho</option>
                <option value="low" <?= $statusFilter == 'low' ? 'selected' : '' ?>>Sắp hết hàng</option>
                <option value="out" <?= $statusFilter == 'out' ? 'selected' : '' ?>>Đã hết hàng</option>
            </select>

            <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-filter"></i> Lọc</button>
            <?php if (!empty($search) || !empty($categoryFilter) || !empty($statusFilter)): ?>
                <a href="products.php" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Bỏ lọc</a>
            <?php endif; ?>
        </form>

        <div style="display: flex; gap: 10px;">
            <button class="btn btn-secondary" onclick="exportTableToCSV('productsTable', 'danh_sach_hang_hoa.csv')">
                <i class="fa-solid fa-file-csv"></i> Xuất CSV
            </button>
            <button class="btn btn-primary" onclick="openModal('addProductModal')">
                <i class="fa-solid fa-plus"></i> Thêm Hàng Hóa Mới
            </button>
        </div>
    </div>

    <!-- Products Table -->
    <div class="glass-panel" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="custom-table" id="productsTable">
                <thead>
                    <tr>
                        <th>Mã SP</th>
                        <th>Tên Mặt Hàng</th>
                        <th>Danh Mục</th>
                        <th>ĐVT</th>
                        <th>Giá Vốn (Nhập)</th>
                        <th>Giá Bán</th>
                        <th>Số Lượng Tồn</th>
                        <th>Trạng Thái</th>
                        <th class="no-export" style="text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="fa-solid fa-box-open" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                                Không tìm thấy sản phẩm nào phù hợp với bộ lọc!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <span style="font-weight: 700; color: #818cf8;"><?= htmlspecialchars($p['product_code']) ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($p['name']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-info" style="font-size: 10px;">
                                        <?= htmlspecialchars($p['category_name'] ?? 'Chưa phân loại') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($p['unit']) ?></td>
                                <td><?= number_format($p['purchase_price'], 0, ',', '.') ?> đ</td>
                                <td style="font-weight: 600; color: #34d399;"><?= number_format($p['sale_price'], 0, ',', '.') ?> đ</td>
                                <td>
                                    <strong><?= number_format($p['stock_quantity']) ?></strong>
                                    <span style="font-size: 11px; color: var(--text-muted);">(Tối thiểu: <?= $p['min_stock'] ?>)</span>
                                </td>
                                <td>
                                    <?php if ($p['stock_quantity'] <= 0): ?>
                                        <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> Hết hàng</span>
                                    <?php elseif ($p['stock_quantity'] <= $p['min_stock']): ?>
                                        <span class="badge badge-warning"><i class="fa-solid fa-triangle-exclamation"></i> Sắp hết</span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Còn hàng</span>
                                    <?php endif; ?>
                                </td>
                                <td class="no-export" style="text-align: right;">
                                    <button class="btn btn-secondary btn-sm" onclick='editProduct(<?= json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <?php if (is_admin()): ?>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>')" title="Xóa">
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

<!-- Modal Them San Pham -->
<div id="addProductModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-plus" style="color: var(--primary);"></i> Thêm Sản Phẩm Mới</h3>
            <button class="modal-close" onclick="closeModal('addProductModal')">&times;</button>
        </div>
        <form action="api/products.php" method="POST">
            <input type="hidden" name="action" value="create">
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Mã Sản Phẩm (*)</label>
                        <input type="text" name="product_code" class="form-control" placeholder="VD: SP-VGA-4060" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tên Mặt Hàng (*)</label>
                        <input type="text" name="name" class="form-control" placeholder="VD: Card đồ họa ASUS RTX 4060 8GB" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Danh Mục Hàng (*)</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Đơn Vị Tính (*)</label>
                        <input type="text" name="unit" class="form-control" placeholder="Chiếc, Hộp, Thùng, Bộ..." value="Chiếc" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Giá Nhập Gốc (VNĐ)</label>
                        <input type="number" name="purchase_price" class="form-control" min="0" step="1000" value="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Giá Bán Đề Xuất (VNĐ)</label>
                        <input type="number" name="sale_price" class="form-control" min="0" step="1000" value="0" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Số Lượng Tồn Ban Đầu</label>
                        <input type="number" name="stock_quantity" class="form-control" min="0" value="10" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ngưỡng Báo Động Hết Hàng</label>
                        <input type="number" name="min_stock" class="form-control" min="1" value="5" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addProductModal')">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Lưu Sản Phẩm</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sua San Pham -->
<div id="editProductModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-pen-to-square" style="color: var(--primary);"></i> Chỉnh Sửa Hàng Hóa</h3>
            <button class="modal-close" onclick="closeModal('editProductModal')">&times;</button>
        </div>
        <form action="api/products.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Mã Sản Phẩm (*)</label>
                        <input type="text" name="product_code" id="edit_product_code" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tên Mặt Hàng (*)</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Danh Mục Hàng (*)</label>
                        <select name="category_id" id="edit_category_id" class="form-control" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Đơn Vị Tính (*)</label>
                        <input type="text" name="unit" id="edit_unit" class="form-control" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Giá Nhập Gốc (VNĐ)</label>
                        <input type="number" name="purchase_price" id="edit_purchase_price" class="form-control" min="0" step="1000" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Giá Bán Đề Xuất (VNĐ)</label>
                        <input type="number" name="sale_price" id="edit_sale_price" class="form-control" min="0" step="1000" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Số Lượng Tồn</label>
                        <input type="number" name="stock_quantity" id="edit_stock_quantity" class="form-control" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ngưỡng Báo Động Hết Hàng</label>
                        <input type="number" name="min_stock" id="edit_min_stock" class="form-control" min="1" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editProductModal')">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Cập Nhật Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
function editProduct(product) {
    document.getElementById('edit_id').value = product.id;
    document.getElementById('edit_product_code').value = product.product_code;
    document.getElementById('edit_name').value = product.name;
    document.getElementById('edit_category_id').value = product.category_id || '';
    document.getElementById('edit_unit').value = product.unit;
    document.getElementById('edit_purchase_price').value = product.purchase_price;
    document.getElementById('edit_sale_price').value = product.sale_price;
    document.getElementById('edit_stock_quantity').value = product.stock_quantity;
    document.getElementById('edit_min_stock').value = product.min_stock;
    openModal('editProductModal');
}

function confirmDelete(id, name) {
    if (confirm(`Bạn có chắc chắn muốn xóa mặt hàng "${name}" không? Thao tác này sẽ xóa các giao dịch liên quan.`)) {
        window.location.href = `api/products.php?action=delete&id=${id}`;
    }
}
</script>

<?php require_once __DIR__ . '/views/footer.php'; ?>
