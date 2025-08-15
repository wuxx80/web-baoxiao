<?php
session_start();
require_once '../../config/config.php';

// 检查是否为管理员以及请求方法
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /views/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expense_id = $_POST['id'] ?? null;
    $rejection_reason = $_POST['reason'] ?? null;

    if ($expense_id && $rejection_reason) {
        try {
            // 更新报销单状态为 rejected 并保存驳回原因
            $stmt = $pdo->prepare("UPDATE expense_reports SET status = 'rejected', rejection_reason = ? WHERE id = ?");
            $stmt->execute([$rejection_reason, $expense_id]);
            http_response_code(200);
            echo json_encode(['success' => true]);
            exit;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid parameters.']);
        exit;
    }
} else {
    // 处理 GET 请求，如果直接访问此页面
    $expense_id = $_GET['id'] ?? null;
    if ($expense_id) {
        // 如果是 GET 请求，可以提供一个简单的表单让管理员填写驳回原因
        // 这里我们假设前端只用 POST 请求，因此 GET 请求直接返回错误
        header('Location: /views/expense_list.php');
        exit;
    }
}