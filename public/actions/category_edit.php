<?php
session_start();
require_once '../../config/config.php';

if ($_SESSION['user']['role'] != 'admin' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../views/categories.php');
    exit;
}

$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? '';

if (!$id || empty($name)) {
    header('Location: ../views/categories.php?error=无效的请求。');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->execute([$name, $id]);
    header('Location: ../views/categories.php?message=分类更新成功。');
    exit;
} catch (PDOException $e) {
    $error = "更新失败: " . $e->getMessage();
    header('Location: ../views/categories.php?error=' . urlencode($error));
    exit;
}