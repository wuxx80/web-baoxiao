<?php
session_start();
require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $nickname = $_POST['nickname'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($password) || empty($nickname) || empty($confirm_password)) {
        header('Location: ../views/register.php?error=所有字段都是必填的。');
        exit;
    }

    if ($password !== $confirm_password) {
        header('Location: ../views/register.php?error=两次输入的密码不一致。');
        exit;
    }

    try {
        // 检查用户名是否已存在
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            header('Location: ../views/register.php?error=用户名已存在。');
            exit;
        }

        // 临时修改为明文密码存储
        $stmt = $pdo->prepare("INSERT INTO users (username, nickname, password_hash, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$username, $nickname, $password]);
        
        header('Location: ../views/login.php?message=注册成功，请登录。');
        exit;
    } catch (PDOException $e) {
        header('Location: ../views/register.php?error=注册失败，请稍后再试。');
        exit;
    }
} else {
    // 非 POST 请求，重定向回注册页面
    header('Location: ../views/register.php');
    exit;
}