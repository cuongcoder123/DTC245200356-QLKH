<?php
/**
 * API Cong cu tao Log thu nghiem cho Grafana Loki & LogQL
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../helpers/auth.php';
require_login();

$action = $_GET['action'] ?? '';
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '192.168.1.105';

switch ($action) {
    case 'test_404':
        write_system_log('WARN', 'HTTP_NOT_FOUND', "Truy cap duong dan khong ton tai: /api/v1/invalid-endpoint-test", [
            'status' => 404,
            'client_ip' => $clientIp
        ]);
        echo json_encode([
            'status' => 'success',
            'message' => 'Đã ghi log HTTP 404 status=404 vào hệ thống log!'
        ]);
        break;

    case 'test_500':
        write_system_log('ERROR', 'HTTP_SERVER_ERROR', "Lỗi kết nối dịch vụ backend ngoại vi: Connection timed out", [
            'status' => 500,
            'client_ip' => $clientIp
        ]);
        echo json_encode([
            'status' => 'success',
            'message' => 'Đã ghi log HTTP 500 status=500 vào hệ thống log!'
        ]);
        break;

    case 'test_tx_error':
        // Ghi log voi dung tu khoa: inventory_transactions va ERROR
        write_system_log('ERROR', 'inventory_transactions', "Xuất kho thất bại: Mã SP SP-CPU-I7 không đủ số lượng tồn trong kho (ERROR: Insufficient stock)", [
            'type' => 'EXPORT',
            'status' => 'FAILED_STOCK',
            'client_ip' => $clientIp
        ]);
        echo json_encode([
            'status' => 'success',
            'message' => 'Đã ghi log {job="app"} |= "inventory_transactions" |= "ERROR" thành công!'
        ]);
        break;

    case 'test_ip_burst':
        for ($i = 1; $i <= 8; $i++) {
            write_system_log('INFO', 'HTTP_REQUEST_BURST', "Burst probe request test #$i tu client_ip", [
                'client_ip' => $clientIp,
                'request_seq' => $i
            ]);
        }
        echo json_encode([
            'status' => 'success',
            'message' => 'Đã sinh 8 burst requests từ IP ' . $clientIp . ' cho LogQL đếm tần suất!'
        ]);
        break;

    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Hành động kiểm thử không hợp lệ'
        ]);
        break;
}
