<?php
/**
 * Helper ghi nhat ky (Structured Logging) cho he thong
 * Ghi dong thoi ra /var/log/app/app.log, MySQL system_logs va stdout/stderr
 */

function write_system_log($level, $action, $message, $extra = []) {
    global $pdo;
    
    $userId = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'anonymous';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $timestamp = date('c');
    
    // 1. Structured JSON Log Object
    $logData = array_merge([
        'timestamp' => $timestamp,
        'level'     => strtoupper($level),
        'event'     => $action,
        'message'   => $message,
        'user'      => $username,
        'user_id'   => $userId,
        'client_ip' => $ip,
        'uri'       => $_SERVER['REQUEST_URI'] ?? ''
    ], $extra);
    
    $jsonLog = json_encode($logData, JSON_UNESCAPED_UNICODE);
    
    // 2. Ghi ra file /var/log/app/app.log (cho Promtail doc)
    $logFile = '/var/log/app/app.log';
    if (!file_exists('/var/log/app')) {
        @mkdir('/var/log/app', 0777, true);
    }
    @file_put_contents($logFile, $jsonLog . PHP_EOL, FILE_APPEND | LOCK_EX);
    
    // 3. Ghi ra stdout/stderr de Docker Logs bat duoc
    error_log("[LOG] " . $jsonLog);
    
    // 4. Luu vao bang system_logs neu CSDL san sang
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $stmt = $pdo->prepare("INSERT INTO system_logs (level, action, message, user_id, ip_address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                strtoupper($level),
                $action,
                $message . (empty($extra) ? '' : ' | ' . json_encode($extra, JSON_UNESCAPED_UNICODE)),
                $userId,
                $ip
            ]);
        } catch (Exception $e) {
            // Khong lam gian doan app neu ghi CSDL loi
        }
    }
}
