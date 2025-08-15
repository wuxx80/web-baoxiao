<?php
session_start();
require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // 检查密码（明文）
            if ($user && $password === $user['password_hash']) {
                $_SESSION['user'] = $user;
                // 登录成功后重定向到公共目录的首页
                header('Location: /index.php');
                exit;
            } else {
                $error = "用户名或密码错误。";
            }
        } catch (PDOException $e) {
            $error = "登录失败，请稍后再试。";
        }
    } else {
        $error = "用户名和密码是必填的。";
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>金瑞员工报销系统 - 登录</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2>金瑞员工报销系统</h2>
            <form action="login.php" method="post">
                <?php if (isset($error)): ?>
                    <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">登录</button>
            </form>
            <div class="login-links">
                <a href="register.php">注册新账号</a>
            </div>
        </div>
    </div>
</body>
</html>