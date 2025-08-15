<?php
session_start();
require_once '../../config/config.php';

// 检查用户是否登录，并且角色为管理员
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login.php');
    exit;
}

$user_id = $_POST['id'] ?? null;
$nickname = $_POST['nickname'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'user';

if (!$user_id || empty($nickname)) {
    header('Location: ../views/users.php?error=数据不完整。');
    exit;
}

try {
    // 检查是否有新密码输入
    if (!empty($password)) {
        // 临时修改为明文密码存储
        $stmt = $pdo->prepare("UPDATE users SET nickname = ?, password_hash = ?, role = ? WHERE id = ?");
        $stmt->execute([$nickname, $password, $role, $user_id]);
    } else {
        // 如果没有新密码，只更新昵称和角色
        $stmt = $pdo->prepare("UPDATE users SET nickname = ?, role = ? WHERE id = ?");
        $stmt->execute([$nickname, $role, $user_id]);
    }
    
    header('Location: ../views/users.php?message=用户信息更新成功。');
    exit;
} catch (PDOException $e) {
    header('Location: ../views/users.php?error=用户信息更新失败，请稍后再试。');
    exit;
}