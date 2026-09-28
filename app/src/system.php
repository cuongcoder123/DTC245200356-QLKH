<?php
/**
 * Trung Tam Giam Sat Ha Tang & Sandbox Thu Nghiem LogQL
 */
require_once __DIR__ . '/helpers/auth.php';
require_login();

$pageTitle = 'Giám Sát Hệ Thống & LogQL';

// Lay 15 log gan nhat tu MySQL
$recentLogs = $pdo->query("SELECT * FROM system_logs ORDER BY created_at DESC LIMIT 15")->fetchAll();

require_once __DIR__ . '/views/header.php';
require_once __DIR__ . '/views/sidebar.php';
require_once __DIR__ . '/views/navbar.php';
?>

<div class="page-content">
    <!-- Header banner -->
    <div style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.2) 0%, rgba(6, 182, 212, 0.1) 100%); border: 1px solid var(--border-hover); border-radius: 16px; padding: 24px; margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h2 style="font-size: 20px; font-weight: 800; color: #fff;">
                    <i class="fa-solid fa-network-wired" style="color: #06b6d4; margin-right: 8px;"></i>
                    Hạ Tầng Giám Sát & Quản Trị Hệ Thống (DevOps / SRE)
                </h2>
                <p style="font-size: 13px; color: var(--text-secondary); margin-top: 6px;">
                    Tích hợp toàn diện Docker, Nginx Reverse Proxy, Prometheus Metrics, Grafana Dashboard & Loki Centralized Logging.
                </p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="/grafana/" target="_blank" class="btn btn-primary">
                    <i class="fa-solid fa-chart-line"></i> Mở Grafana
                </a>
                <a href="/pma/" target="_blank" class="btn btn-secondary">
                    <i class="fa-solid fa-database"></i> Mở phpMyAdmin
                </a>
            </div>
        </div>
    </div>

    <!-- 4 Architecture Component Cards -->
    <div class="grid-cards" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="kpi-card">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #34d399; text-transform: uppercase;">Port 80 / 443</div>
                <div style="font-size: 16px; font-weight: 700; margin: 4px 0;">Nginx Gateway</div>
                <div style="font-size: 12px; color: var(--text-muted);">SSL Self-signed, Security Headers, Reverse Proxy</div>
            </div>
            <div class="kpi-icon success"><i class="fa-solid fa-shield-halved"></i></div>
        </div>

        <div class="kpi-card">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #38bdf8; text-transform: uppercase;">Port 3306 (Internal)</div>
                <div style="font-size: 16px; font-weight: 700; margin: 4px 0;">MySQL 8.0 Server</div>
                <div style="font-size: 12px; color: var(--text-muted);">Mạng backend-net cô lập an toàn</div>
            </div>
            <div class="kpi-icon primary"><i class="fa-solid fa-database"></i></div>
        </div>

        <div class="kpi-card">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #f97316; text-transform: uppercase;">Port 9090 / 3000</div>
                <div style="font-size: 16px; font-weight: 700; margin: 4px 0;">Prometheus & Grafana</div>
                <div style="font-size: 12px; color: var(--text-muted);">Scrape Node, MySQL, Container Metrics</div>
            </div>
            <div class="kpi-icon warning"><i class="fa-solid fa-chart-area"></i></div>
        </div>

        <div class="kpi-card">
            <div>
                <div style="font-size: 11px; font-weight: 700; color: #a855f7; text-transform: uppercase;">Port 3100 / 9080</div>
                <div style="font-size: 16px; font-weight: 700; margin: 4px 0;">Loki & Promtail</div>
                <div style="font-size: 12px; color: var(--text-muted);">Gom log Nginx, App và truy vấn LogQL</div>
            </div>
            <div class="kpi-icon danger"><i class="fa-solid fa-layer-group"></i></div>
        </div>
    </div>

    <!-- LogQL Showcase and Sandbox Tester -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
        <!-- LogQL Requirement Demonstration -->
        <div class="glass-panel">
            <div class="panel-header">
                <h3 class="panel-title"><i class="fa-solid fa-magnifying-glass-chart" style="color: #a855f7;"></i> 3 Câu Truy Vấn LogQL (Chương 6)</h3>
            </div>
            <div style="font-size: 13px; display: flex; flex-direction: column; gap: 14px;">
                <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px;">
                    <div style="font-weight: 700; color: #f87171; margin-bottom: 4px;">Query 1: Lọc mã lỗi HTTP 4xx hoặc 5xx</div>
                    <code style="display: block; background: #0f172a; padding: 8px; border-radius: 6px; color: #38bdf8; font-family: monospace;">
                        {job="nginx"} |= "status=" |~ "status=[45][0-9][0-9]"
                    </code>
                    <div style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">Phát hiện các request bị lỗi 403, 404 hoặc 500 từ gateway.</div>
                </div>

                <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px;">
                    <div style="font-weight: 700; color: #fbbf24; margin-bottom: 4px;">Query 2: Thống kê tần suất request theo IP (Phát hiện DDoS/Brute-force)</div>
                    <code style="display: block; background: #0f172a; padding: 8px; border-radius: 6px; color: #34d399; font-family: monospace;">
                        sum by (client_ip) (count_over_time({job="nginx"} | json [5m]))
                    </code>
                    <div style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">Đếm số lượng request từng client trong cửa sổ 5 phút.</div>
                </div>

                <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px;">
                    <div style="font-weight: 700; color: #a78bfa; margin-bottom: 4px;">Query 3: Truy vết lỗi giao dịch kho hàng (Inventory Transactions)</div>
                    <code style="display: block; background: #0f172a; padding: 8px; border-radius: 6px; color: #f472b6; font-family: monospace;">
                        {job="app"} |= "inventory_transactions" |= "ERROR"
                    </code>
                    <div style="color: var(--text-muted); font-size: 11px; margin-top: 4px;">Truy vấn trực tiếp các lỗi xuất vượt tồn hoặc vi phạm ràng buộc dữ liệu.</div>
                </div>
            </div>
        </div>

        <!-- Interactive Sandbox: Trigger Test Logs for Loki -->
        <div class="glass-panel">
            <div class="panel-header">
                <h3 class="panel-title"><i class="fa-solid fa-flask" style="color: #38bdf8;"></i> Công Cụ Sinh Log Kiểm Thử (Log Generator)</h3>
                <span class="badge badge-warning">Testing Sandbox</span>
            </div>
            <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 16px;">
                Bấm các nút dưới đây để tạo log thực tế ghi vào <code>/var/log/nginx/</code> và <code>/var/log/app/app.log</code>. Promtail sẽ lập tức đẩy vào Loki để kiểm tra trên Grafana!
            </p>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <button class="btn btn-secondary" onclick="triggerTestLog('test_404')" style="justify-content: space-between;">
                    <span><i class="fa-solid fa-triangle-exclamation" style="color: #f87171;"></i> Sinh Log HTTP 404 Not Found (Cho Query 1)</span>
                    <i class="fa-solid fa-paper-plane"></i>
                </button>

                <button class="btn btn-secondary" onclick="triggerTestLog('test_500')" style="justify-content: space-between;">
                    <span><i class="fa-solid fa-bug" style="color: #ef4444;"></i> Sinh Log HTTP 500 Server Error (Cho Query 1)</span>
                    <i class="fa-solid fa-paper-plane"></i>
                </button>

                <button class="btn btn-secondary" onclick="triggerTestLog('test_tx_error')" style="justify-content: space-between;">
                    <span><i class="fa-solid fa-circle-xmark" style="color: #f43f5e;"></i> Sinh Log inventory_transactions ERROR (Cho Query 3)</span>
                    <i class="fa-solid fa-paper-plane"></i>
                </button>

                <button class="btn btn-secondary" onclick="triggerTestLog('test_ip_burst')" style="justify-content: space-between;">
                    <span><i class="fa-solid fa-bolt" style="color: #fbbf24;"></i> Sinh Burst Requests từ Client IP (Cho Query 2)</span>
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Live System Logs Table -->
    <div class="glass-panel" style="padding: 0; overflow: hidden;">
        <div class="panel-header" style="padding: 20px 24px 10px 24px; border-bottom: none;">
            <h3 class="panel-title"><i class="fa-solid fa-terminal"></i> Nhật Ký Hoạt Động Hệ Thống Gần Đây</h3>
            <button class="btn btn-secondary btn-sm" onclick="location.reload()"><i class="fa-solid fa-rotate-right"></i> Làm mới</button>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Cấp Độ</th>
                        <th>Hành Động (Event)</th>
                        <th>Nội Dung Thông Điệp</th>
                        <th>Địa Chỉ IP</th>
                        <th>Thời Gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentLogs as $l): ?>
                        <tr>
                            <td>
                                <?php if ($l['level'] === 'ERROR'): ?>
                                    <span class="badge badge-danger">ERROR</span>
                                <?php elseif ($l['level'] === 'WARN'): ?>
                                    <span class="badge badge-warning">WARN</span>
                                <?php else: ?>
                                    <span class="badge badge-success">INFO</span>
                                <?php endif; ?>
                            </td>
                            <td><strong style="color: #818cf8;"><?= htmlspecialchars($l['action']) ?></strong></td>
                            <td style="font-family: monospace; font-size: 12px;"><?= htmlspecialchars($l['message']) ?></td>
                            <td><?= htmlspecialchars($l['ip_address'] ?? '127.0.0.1') ?></td>
                            <td style="color: var(--text-muted); font-size: 11px;"><?= date('d/m/Y H:i:s', strtotime($l['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/footer.php'; ?>
