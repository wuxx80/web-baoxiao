<?php
session_start();
require_once '../../config/config.php';

if (!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../views/login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$nickname = $_POST['nickname'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (empty($nickname)) {
    header('Location: ../views/user_profile.php?error=昵称不能为空。');
    exit;
}

try {
    if (!empty($new_password)) {
        // 临时修改为明文密码存储
        $stmt = $pdo->prepare("UPDATE users SET nickname = ?, password_hash = ? WHERE id = ?");
        $stmt->execute([$nickname, $new_password, $user_id]);
    } else {
        // 只更新昵称
        $stmt = $pdo->prepare("UPDATE users SET nickname = ? WHERE id = ?");
        $stmt->execute([$nickname, $user_id]);
    }

    // 更新成功后，更新 session 中的昵称
    $_SESSION['user']['nickname'] = $nickname;

    header('Location: ../views/user_profile.php?message=个人资料更新成功。');
    exit;
} catch (PDOException $e) {
    error_log("Profile update failed: " . $e->getMessage());
    header('Location: ../views/user_profile.php?error=更新失败，请稍后再试。');
    exit;
}