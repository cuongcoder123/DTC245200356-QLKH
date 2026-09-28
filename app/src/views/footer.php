<?php
/**
 * Footer Template
 */
?>
</main>
</div>

<!-- Modal Nhap kho nhanh toan cuc -->
<div id="quickImportModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-arrow-down" style="color: #10b981;"></i> Lập Phiếu Nhập Kho Nhanh</h3>
            <button class="modal-close" onclick="closeModal('quickImportModal')">&times;</button>
        </div>
        <form action="api/transactions.php" method="POST">
            <input type="hidden" name="type" value="IMPORT">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nhà cung cấp (*)</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">-- Chọn Nhà Cung Cấp --</option>
                        <?php
                        global $pdo;
                        if (isset($pdo)) {
                            $sups = $pdo->query("SELECT id, name FROM suppliers ORDER BY name ASC")->fetchAll();
                            foreach ($sups as $s) {
                                echo "<option value='{$s['id']}'>" . htmlspecialchars($s['name']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sản phẩm nhập (*)</label>
                    <select name="product_id" class="form-control" required id="quick_import_prod">
                        <option value="">-- Chọn Sản Phẩm --</option>
                        <?php
                        if (isset($pdo)) {
                            $prods = $pdo->query("SELECT id, product_code, name, purchase_price, unit FROM products ORDER BY name ASC")->fetchAll();
                            foreach ($prods as $p) {
                                echo "<option value='{$p['id']}' data-price='{$p['purchase_price']}'>[{$p['product_code']}] " . htmlspecialchars($p['name']) . " ({$p['unit']})</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Số lượng nhập (*)</label>
                        <input type="number" name="quantity" class="form-control" min="1" value="10" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Đơn giá nhập (VNĐ) (*)</label>
                        <input type="number" name="unit_price" id="quick_import_price" class="form-control" min="0" step="1000" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Ghi chú giao dịch</label>
                    <textarea name="note" class="form-control" rows="2" placeholder="VD: Nhập bổ sung đợt mới từ nhà phân phối"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('quickImportModal')">Hủy</button>
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Xác Nhận Nhập Kho</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Xuat kho nhanh toan cuc -->
<div id="quickExportModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-arrow-up" style="color: #4f46e5;"></i> Lập Phiếu Xuất Kho Nhanh</h3>
            <button class="modal-close" onclick="closeModal('quickExportModal')">&times;</button>
        </div>
        <form action="api/transactions.php" method="POST">
            <input type="hidden" name="type" value="EXPORT">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Sản phẩm xuất (*)</label>
                    <select name="product_id" class="form-control" required id="quick_export_prod">
                        <option value="">-- Chọn Sản Phẩm --</option>
                        <?php
                        if (isset($pdo)) {
                            $prods = $pdo->query("SELECT id, product_code, name, sale_price, stock_quantity, unit FROM products ORDER BY name ASC")->fetchAll();
                            foreach ($prods as $p) {
                                echo "<option value='{$p['id']}' data-price='{$p['sale_price']}' data-stock='{$p['stock_quantity']}'>[{$p['product_code']}] " . htmlspecialchars($p['name']) . " (Tồn: {$p['stock_quantity']} {$p['unit']})</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Số lượng xuất (*)</label>
                        <input type="number" name="quantity" id="quick_export_qty" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Đơn giá bán (VNĐ) (*)</label>
                        <input type="number" name="unit_price" id="quick_export_price" class="form-control" min="0" step="1000" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Lý do xuất / Người nhận hàng (*)</label>
                    <textarea name="note" class="form-control" rows="2" placeholder="VD: Xuất bán cho khách hàng hoặc lắp đặt chi nhánh..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('quickExportModal')">Hủy</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Xác Nhận Xuất Kho</button>
            </div>
        </form>
    </div>
</div>

<script>
// Tu dong dien don gia khi chon san pham trong modal
document.getElementById('quick_import_prod')?.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const price = selected.getAttribute('data-price');
    if (price) document.getElementById('quick_import_price').value = price;
});

document.getElementById('quick_export_prod')?.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const price = selected.getAttribute('data-price');
    if (price) document.getElementById('quick_export_price').value = price;
});
</script>

<script src="assets/js/app.js"></script>
</body>
</html>
