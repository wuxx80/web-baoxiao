<?php
session_start();
require_once '../../config/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');

    if ($id && !empty($name)) {
        try {
            $stmt = $pdo->prepare("UPDATE expense_categories SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            header('Location: /views/admin/manage_categories.php');
            exit;
        } catch (PDOException $e) {
            // 在生产环境中应记录错误而非直接显示
            echo '更新失败：' . $e->getMessage();
            exit;
        }
    } else {
        header('Location: /views/admin/manage_categories.php');
        exit;
    }
}
?>