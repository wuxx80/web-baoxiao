<?php
session_start();
require_once '../../config/config.php';

// 检查用户是否登录，并且角色为管理员
if ($_SESSION['user']['role'] != 'admin' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../views/users.php');
    exit;
}

$username = $_POST['username'] ?? '';
$nickname = $_POST['nickname'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'user';

if (!empty($username) && !empty($password) && !empty($nickname)) {
    try {
        // 检查用户名是否已存在
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            header('Location: ../views/user_add.php?error=用户名已存在。');
            exit;
        }

        // 临时修改为明文密码存储
        $stmt = $pdo->prepare("INSERT INTO users (username, nickname, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $nickname, $password, $role]);
        
        header('Location: ../views/users.php?message=用户添加成功。');
        exit;
    } catch (PDOException $e) {
        $error = "添加失败: " . $e->getMessage();
        header('Location: ../views/users.php?error=' . urlencode($error));
        exit;
    }
} else {
    header('Location: ../views/user_add.php?error=所有字段都是必填的。');
    exit;
}