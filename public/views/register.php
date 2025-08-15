<?php
session_start();
require_once '../../config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $nickname = $_POST['nickname'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($nickname) || empty($password) || empty($confirm_password)) {
        $error = '所有字段都是必填的。';
    } elseif ($password !== $confirm_password) {
        $error = '两次输入的密码不匹配。';
    } else {
        try {
            // 检查用户名是否已存在
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $error = '用户名已存在，请选择其他用户名。';
            } else {
                // 默认新注册用户为普通用户
                $stmt = $pdo->prepare("INSERT INTO users (username, nickname, password_hash, role) VALUES (?, ?, ?, 'user')");
                $stmt->execute([$username, $nickname, $password]);
                $success = '注册成功！请返回登录页面。';
            }
        } catch (PDOException $e) {
            $error = "注册失败，请稍后再试。";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>金瑞员工报销系统 - 注册</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2>注册新账号</h2>
            <form action="register.php" method="post">
                <?php if ($error): ?>
                    <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
                <?php elseif ($success): ?>
                    <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="nickname">昵称</label>
                    <input type="text" id="nickname" name="nickname" required>
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">确认密码</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">注册</button>
            </form>
            <div class="login-links">
                <a href="login.php">已有账号？立即登录</a>
            </div>
        </div>
    </div>
</body>
</html>