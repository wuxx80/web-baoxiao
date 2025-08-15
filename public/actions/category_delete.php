<?php
session_start();
require_once '../../config/config.php';

if ($_SESSION['user']['role'] != 'admin') {
    header('Location: ../views/categories.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: ../views/categories.php?error=未指定分类ID。');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: ../views/categories.php?message=分类删除成功。');
    exit;
} catch (PDOException $e) {
    $error = "删除失败: " . $e->getMessage();
    header('Location: ../views/categories.php?error=' . urlencode($error));
    exit;
}