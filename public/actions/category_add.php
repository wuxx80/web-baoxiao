<?php
session_start();
require_once '../../config/config.php';

if ($_SESSION['user']['role'] != 'admin' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../views/categories.php');
    exit;
}

$name = $_POST['name'] ?? '';

if (!empty($name)) {
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        header('Location: ../views/categories.php?message=分类添加成功。');
        exit;
    } catch (PDOException $e) {
        $error = "添加失败: " . $e->getMessage();
        header('Location: ../views/categories.php?error=' . urlencode($error));
        exit;
    }
}

header('Location: ../views/categories.php?error=分类名称不能为空。');
exit;