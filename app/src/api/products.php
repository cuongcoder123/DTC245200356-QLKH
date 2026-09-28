<?php
/**
 * API Xu ly CRUD San Pham
 */
require_once __DIR__ . '/../helpers/auth.php';
require_login();

$action = $_REQUEST['action'] ?? '';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['product_code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $catId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $unit = trim($_POST['unit'] ?? 'Chiếc');
    $pPrice = (float)($_POST['purchase_price'] ?? 0);
    $sPrice = (float)($_POST['sale_price'] ?? 0);
    $stock = (int)($_POST['stock_quantity'] ?? 0);
    $minStock = (int)($_POST['min_stock'] ?? 5);

    try {
        $stmt = $pdo->prepare("
            INSERT INTO products (product_code, name, category_id, unit, purchase_price, sale_price, stock_quantity, min_stock)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$code, $name, $catId, $unit, $pPrice, $sPrice, $stock, $minStock]);
        
        write_system_log('INFO', 'PRODUCT_CREATE', "Tạo mới sản phẩm {$code} - {$name}", [
            'product_code' => $code,
            'initial_stock' => $stock
        ]);
        header('Location: ../products.php?msg=created');
        exit;
    } catch (PDOException $e) {
        write_system_log('ERROR', 'PRODUCT_CREATE_FAILED', "Lỗi tạo sản phẩm {$code}: " . $e->getMessage());
        die("Lỗi khi thêm sản phẩm: " . htmlspecialchars($e->getMessage()));
    }
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $code = trim($_POST['product_code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $catId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $unit = trim($_POST['unit'] ?? 'Chiếc');
    $pPrice = (float)($_POST['purchase_price'] ?? 0);
    $sPrice = (float)($_POST['sale_price'] ?? 0);
    $stock = (int)($_POST['stock_quantity'] ?? 0);
    $minStock = (int)($_POST['min_stock'] ?? 5);

    try {
        $stmt = $pdo->prepare("
            UPDATE products 
            SET product_code = ?, name = ?, category_id = ?, unit = ?, purchase_price = ?, sale_price = ?, stock_quantity = ?, min_stock = ?
            WHERE id = ?
        ");
        $stmt->execute([$code, $name, $catId, $unit, $pPrice, $sPrice, $stock, $minStock, $id]);

        write_system_log('INFO', 'PRODUCT_UPDATE', "Cập nhật sản phẩm ID {$id} ({$code})", [
            'product_id' => $id,
            'updated_stock' => $stock
        ]);
        header('Location: ../products.php?msg=updated');
        exit;
    } catch (PDOException $e) {
        write_system_log('ERROR', 'PRODUCT_UPDATE_FAILED', "Lỗi cập nhật sản phẩm ID {$id}: " . $e->getMessage());
        die("Lỗi khi cập nhật sản phẩm: " . htmlspecialchars($e->getMessage()));
    }
}

if ($action === 'delete') {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);

        write_system_log('WARN', 'PRODUCT_DELETE', "Xóa sản phẩm ID {$id}", ['deleted_id' => $id]);
        header('Location: ../products.php?msg=deleted');
        exit;
    } catch (PDOException $e) {
        write_system_log('ERROR', 'PRODUCT_DELETE_FAILED', "Lỗi xóa sản phẩm ID {$id}: " . $e->getMessage());
        die("Lỗi khi xóa sản phẩm: " . htmlspecialchars($e->getMessage()));
    }
}

header('Location: ../products.php');
exit;
