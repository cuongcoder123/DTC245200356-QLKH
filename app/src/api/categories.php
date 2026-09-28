<?php
require_once __DIR__ . '/../helpers/auth.php';
require_login();

$action = $_REQUEST['action'] ?? '';

if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $desc]);
        write_system_log('INFO', 'CATEGORY_CREATE', "Tạo mới danh mục: $name");
        header('Location: ../categories.php?msg=created');
        exit;
    } catch (PDOException $e) {
        write_system_log('ERROR', 'CATEGORY_CREATE_FAILED', "Lỗi tạo danh mục: " . $e->getMessage());
        die("Lỗi: " . htmlspecialchars($e->getMessage()));
    }
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    try {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $desc, $id]);
        write_system_log('INFO', 'CATEGORY_UPDATE', "Cập nhật danh mục ID: $id ($name)");
        header('Location: ../categories.php?msg=updated');
        exit;
    } catch (PDOException $e) {
        write_system_log('ERROR', 'CATEGORY_UPDATE_FAILED', "Lỗi cập nhật danh mục: " . $e->getMessage());
        die("Lỗi: " . htmlspecialchars($e->getMessage()));
    }
}

if ($action === 'delete') {
    require_admin();
    $id = (int)($_GET['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        write_system_log('WARN', 'CATEGORY_DELETE', "Xóa danh mục ID: $id");
        header('Location: ../categories.php?msg=deleted');
        exit;
    } catch (PDOException $e) {
        write_system_log('ERROR', 'CATEGORY_DELETE_FAILED', "Lỗi xóa danh mục: " . $e->getMessage());
        die("Lỗi: " . htmlspecialchars($e->getMessage()));
    }
}

header('Location: ../categories.php');
exit;
