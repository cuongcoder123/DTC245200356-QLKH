<?php
/**
 * Ket noi Co so Du lieu MySQL su dung PDO
 * He thong Quan Ly Kho Hang - DTC245200356
 */

$db_host = getenv('DB_HOST') ?: 'db';
$db_name = getenv('DB_NAME') ?: 'inventory_db';
$db_user = getenv('DB_USER') ?: 'inventory_user';
$db_pass = getenv('DB_PASS') ?: 'inventory_pass_2026';
$db_port = getenv('DB_PORT') ?: '3306';

$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // Log loi ket noi
    error_log(json_encode([
        'time' => date('c'),
        'level' => 'ERROR',
        'event' => 'database_connection_failed',
        'message' => $e->getMessage()
    ]));
    
    // Hien thi giao dien thong bao loi than thien
    die("
    <div style='font-family: sans-serif; text-align: center; padding: 50px;'>
        <h2 style='color: #e53e3e;'>⚠️ Không thể kết nối Cơ sở Dữ liệu MySQL</h2>
        <p>Vui lòng kiểm tra container MySQL đang chạy và thông số kết nối.</p>
        <p style='color: #718096; font-size: 13px;'>Chi tiết: " . htmlspecialchars($e->getMessage()) . "</p>
    </div>
    ");
}
