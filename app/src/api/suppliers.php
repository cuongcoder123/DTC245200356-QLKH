<?php
require_once __DIR__ . '/../helpers/auth.php';
require_login();

$action = $_REQUEST['action'] ?? '';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    try {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, phone, email, address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $phone, $email, $address]);
        write_system_log('INFO', 'SUPPLIER_CREATE', "Thêm nhà cung cấp mới: $name");
        header('Location: ../suppliers.php?msg=created');
        exit;
    } catch (PDOException $e) {
        write_system_log('ERROR', 'SUPPLIER_CREATE_FAILED', "Lỗi tạo nhà cung cấp: " . $e->getMessage());
        die("Lỗi: " . htmlspecialchars($e->getMessage()));
    }
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    try {
        $stmt = $pdo->prepare("UPDATE suppliers SET name = ?, phone = ?, email = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $email, $address, $id]);
        write_system_log('INFO', 'SUPPLIER_UPDATE', "Cập nhật nhà cung cấp ID: $id ($name)");
        header('Location: ../suppliers.php?msg=updated');
        exit;
    } catch (PDOException $e) {
        write_system_log('ERROR', 'SUPPLIER_UPDATE_FAILED', "Lỗi cập nhật nhà cung cấp: " . $e->getMessage());
        die("Lỗi: " . htmlspecialchars($e->getMessage()));
    }
}

if ($action === 'delete') {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);
        write_system_log('WARN', 'SUPPLIER_DELETE', "Xóa nhà cung cấp ID: $id");
        header('Location: ../suppliers.php?msg=deleted');
        exit;
    } catch (PDOException $e) {
        write_system_log('ERROR', 'SUPPLIER_DELETE_FAILED', "Lỗi xóa nhà cung cấp: " . $e->getMessage());
        die("Lỗi: " . htmlspecialchars($e->getMessage()));
    }
}

header('Location: ../suppliers.php');
exit;
