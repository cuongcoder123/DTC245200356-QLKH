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
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
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
    header('Content-Type: text/html; charset=UTF-8');
    die("
    <!DOCTYPE html>
    <html lang='vi'>
    <head><meta charset='UTF-8'><title>Lỗi Kết Nối CSDL</title></head>
    <body style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; background: #0b0f19; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0;'>
        <div style='background: #131d31; border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 40px; max-width: 500px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.5);'>
            <h2 style='color: #ef4444; margin-top: 0;'>⚠️ Không thể kết nối Cơ sở Dữ liệu MySQL</h2>
            <p style='color: #94a3b8; line-height: 1.6;'>Vui lòng kiểm tra container MySQL đang chạy và thông số kết nối.</p>
            <p style='background: #0f172a; padding: 10px; border-radius: 6px; color: #f87171; font-size: 13px; font-family: monospace; word-break: break-all; margin-top: 15px;'>Chi tiết: " . htmlspecialchars($e->getMessage()) . "</p>
        </div>
    </body>
    </html>
    ");
}
