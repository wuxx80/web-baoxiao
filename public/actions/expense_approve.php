<?php
session_start();
require_once '../../config/config.php';

// 检查用户是否登录，并且角色为管理员
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit;
}

$report_id = $_GET['id'] ?? null;

if (!$report_id) {
    header('Location: ../views/expense_list.php?error=未指定报销单ID。');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE expense_reports SET status = 'approved' WHERE id = ?");
    $stmt->execute([$report_id]);
    
    header('Location: ../views/expense_list.php?message=报销单已成功批准。');
    exit;
} catch (PDOException $e) {
    header('Location: ../views/expense_list.php?error=批准失败，请稍后再试。');
    exit;
}