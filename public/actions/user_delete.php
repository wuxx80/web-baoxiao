<?php
session_start();
require_once '../../config/config.php';

if ($_SESSION['user']['role'] != 'admin') {
    header('Location: ../views/users.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: ../views/users.php?error=未指定用户ID。');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: ../views/users.php?message=用户删除成功。');
    exit;
} catch (PDOException $e) {
    $error = "删除失败: " . $e->getMessage();
    header('Location: ../views/users.php?error=' . urlencode($error));
    exit;
}