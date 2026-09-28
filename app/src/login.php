<?php
/**
 * Trang Dang Nhap He Thong Quan Ly Kho Hang
 */
require_once __DIR__ . '/helpers/auth.php';

$error = '';
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!';
    } else {
        if (login_user($username, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Tài khoản hoặc mật khẩu không chính xác!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | Hệ thống Quản lý Kho hàng DTC245200356</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: radial-gradient(circle at 50% 20%, #1e1b4b 0%, #0b0f19 80%);
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 440px;
            background: rgba(19, 29, 49, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            padding: 36px 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 30px rgba(79, 70, 229, 0.2);
            animation: fadeIn 0.4s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-logo {
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
            border-radius: 16px;
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: #fff;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        }
        .quick-accounts {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        .account-badge-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 14px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px dashed var(--border-color);
            border-radius: var(--radius-sm);
            margin-bottom: 8px;
            cursor: pointer;
            transition: var(--transition);
        }
        .account-badge-btn:hover {
            background: rgba(79, 70, 229, 0.1);
            border-color: var(--primary);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-brand">
        <div class="login-logo">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <h2 style="font-size: 22px; font-weight: 800; color: #fff;">SMART INVENTORY</h2>
        <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
            Đồ Án Tốt Nghiệp / Bài Tập Lớn - Đề 05
        </p>
    </div>

    <?php if ($error): ?>
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); color: #f87171; padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label class="form-label"><i class="fa-regular fa-user"></i> Tên đăng nhập</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="admin hoặc staff" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label"><i class="fa-solid fa-lock"></i> Mật khẩu</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px; margin-top: 10px;">
            <i class="fa-solid fa-right-to-bracket"></i> Đăng Nhập Hệ Thống
        </button>
    </form>

    <div class="quick-accounts">
        <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px; letter-spacing: 0.05em;">
            Tài khoản mẫu (Bấm để điền nhanh):
        </div>

        <div class="account-badge-btn" onclick="fillCredentials('admin', 'Admin@123456')">
            <div>
                <strong style="color: #f87171; font-size: 12px;">Admin (Toàn quyền):</strong>
                <span style="font-size: 12px; color: var(--text-secondary); margin-left: 6px;">admin / Admin@123456</span>
            </div>
            <i class="fa-solid fa-arrow-turn-down" style="color: var(--primary);"></i>
        </div>

        <div class="account-badge-btn" onclick="fillCredentials('staff', 'Staff@123456')">
            <div>
                <strong style="color: #34d399; font-size: 12px;">Thủ kho (Staff):</strong>
                <span style="font-size: 12px; color: var(--text-secondary); margin-left: 6px;">staff / Staff@123456</span>
            </div>
            <i class="fa-solid fa-arrow-turn-down" style="color: var(--accent);"></i>
        </div>
    </div>
</div>

<script>
function fillCredentials(user, pass) {
    document.getElementById('username').value = user;
    document.getElementById('password').value = pass;
}
</script>

</body>
</html>
