<?php
/**
 * API Xu ly Lap Phieu Nhap / Xuat Kho
 */
require_once __DIR__ . '/../helpers/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../transactions.php');
    exit;
}

$type = strtoupper($_POST['type'] ?? 'IMPORT');
$productId = (int)($_POST['product_id'] ?? 0);
$supplierId = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
$quantity = (int)($_POST['quantity'] ?? 0);
$unitPrice = (float)($_POST['unit_price'] ?? 0);
$note = trim($_POST['note'] ?? '');
$redirect = $_POST['redirect'] ?? '../transactions.php';
$userId = $_SESSION['user_id'] ?? 1;

if ($productId <= 0 || $quantity <= 0) {
    die("Dữ liệu sản phẩm hoặc số lượng không hợp lệ!");
}

try {
    $pdo->beginTransaction();

    // 1. Kiem tra san pham ton tai
    $stmt = $pdo->prepare("SELECT id, name, product_code, stock_quantity FROM products WHERE id = ? FOR UPDATE");
    $stmt->execute([$productId]);
    $prod = $stmt->fetch();

    if (!$prod) {
        throw new Exception("Sản phẩm không tồn tại trong hệ thống!");
    }

    $totalAmount = $quantity * $unitPrice;
    $datePrefix = date('Ymd');

    // 2. Xu ly theo tung loai giao dich
    if ($type === 'IMPORT') {
        // Sinh ma phieu Nhap
        $code = 'NK-' . $datePrefix . '-' . strtoupper(substr(uniqid(), -4));

        // Tang ton kho san pham
        $updateStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
        $updateStock->execute([$quantity, $productId]);

        // Luu ban ghi giao dich
        $stmtIns = $pdo->prepare("
            INSERT INTO inventory_transactions (transaction_code, type, product_id, quantity, unit_price, total_amount, supplier_id, note, created_by)
            VALUES (?, 'IMPORT', ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtIns->execute([$code, $productId, $quantity, $unitPrice, $totalAmount, $supplierId, $note, $userId]);

        $pdo->commit();

        write_system_log('INFO', 'inventory_transactions', "Nhập kho thành công phiếu {$code}: +{$quantity} {$prod['name']}", [
            'transaction_code' => $code,
            'type' => 'IMPORT',
            'product_id' => $productId,
            'product_code' => $prod['product_code'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount
        ]);

        header("Location: $redirect?msg=import_success");
        exit;

    } elseif ($type === 'EXPORT') {
        // Sinh ma phieu Xuat
        $code = 'XK-' . $datePrefix . '-' . strtoupper(substr(uniqid(), -4));

        // Kiem tra so luong ton co du de xuat khong
        if ($prod['stock_quantity'] < $quantity) {
            $pdo->rollBack();
            // GHI LOG ERROR CHO LOGQL TRUY VAN
            write_system_log('ERROR', 'inventory_transactions', "Xuất kho thất bại: Vượt quá số lượng tồn kho (Yêu cầu: {$quantity}, Hiện còn: {$prod['stock_quantity']})", [
                'type' => 'EXPORT',
                'product_id' => $productId,
                'product_code' => $prod['product_code'],
                'requested_qty' => $quantity,
                'available_stock' => $prod['stock_quantity']
            ]);

            header('Content-Type: text/html; charset=UTF-8');
            die("
            <!DOCTYPE html>
            <html lang='vi'>
            <head><meta charset='UTF-8'><title>Lỗi Xuất Kho</title></head>
            <body style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; background: #0b0f19; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0;'>
                <div style='background: #131d31; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 40px; max-width: 520px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.5);'>
                    <h2 style='color: #ef4444; margin-top: 0;'>⚠️ Lỗi: Không Đủ Số Lượng Tồn Kho!</h2>
                    <p style='color: #cbd5e1; margin-bottom: 15px;'>Sản phẩm: <strong>{$prod['name']}</strong> ({$prod['product_code']})</p>
                    <p style='color: #94a3b8;'>Số lượng tồn hiện có: <strong style='color:#ef4444;'>{$prod['stock_quantity']}</strong> | Yêu cầu xuất: <strong style='color:#38bdf8;'>{$quantity}</strong></p>
                    <a href='{$redirect}' style='display:inline-block; margin-top:20px; padding:10px 24px; background:#4f46e5; color:#fff; text-decoration:none; border-radius:8px; font-weight:600;'>Quay lại giao dịch</a>
                </div>
            </body>
            </html>
            ");
        }

        // Giam ton kho
        $updateStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        $updateStock->execute([$quantity, $productId]);

        // Luu ban ghi giao dich xuat
        $stmtIns = $pdo->prepare("
            INSERT INTO inventory_transactions (transaction_code, type, product_id, quantity, unit_price, total_amount, supplier_id, note, created_by)
            VALUES (?, 'EXPORT', ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtIns->execute([$code, $productId, $quantity, $unitPrice, $totalAmount, null, $note, $userId]);

        $pdo->commit();

        write_system_log('INFO', 'inventory_transactions', "Xuất kho thành công phiếu {$code}: -{$quantity} {$prod['name']}", [
            'transaction_code' => $code,
            'type' => 'EXPORT',
            'product_id' => $productId,
            'product_code' => $prod['product_code'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount
        ]);

        header("Location: $redirect?msg=export_success");
        exit;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    write_system_log('ERROR', 'inventory_transactions', "Lỗi xử lý giao dịch kho: " . $e->getMessage());
    die("Lỗi hệ thống: " . htmlspecialchars($e->getMessage()));
}
