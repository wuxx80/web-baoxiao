<?php
session_start();
require_once '../../config/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../views/login.php');
    exit;
}

$user_role = $_SESSION['user']['role'];
$current_user_id = $_SESSION['user']['id'];

$report_ids = [];

// 检查是单个删除（GET）还是批量删除（POST）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_ids'])) {
    // 批量删除
    $report_ids = $_POST['report_ids'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // 单个删除
    $report_ids[] = $_GET['id'];
}

if (empty($report_ids)) {
    header('Location: ../views/expense_list.php?error=未指定报销单ID。');
    exit;
}

try {
    // 开始事务
    $pdo->beginTransaction();

    foreach ($report_ids as $report_id) {
        // 检查权限：管理员可以删除所有，普通用户只能删除自己的待处理报销单
        $stmt = $pdo->prepare("SELECT user_id, status FROM expense_reports WHERE id = ?");
        $stmt->execute([$report_id]);
        $report = $stmt->fetch();

        if ($report) {
            // 确保只有管理员或普通用户删除自己的待处理报销单
            if ($user_role !== 'admin' && ($user_role === 'user' && ($report['user_id'] != $current_user_id || $report['status'] !== 'pending'))) {
                // 如果是普通用户，且报销单不属于自己或已不是待处理状态，则跳过
                continue;
            }
            
            // 删除报销单，数据库的级联删除会自动删除关联的费用项目
            $stmt_delete = $pdo->prepare("DELETE FROM expense_reports WHERE id = ?");
            $stmt_delete->execute([$report_id]);
        }
    }

    // 提交事务
    $pdo->commit();
    
    $redirect_url = ($_SERVER['REQUEST_METHOD'] === 'POST') ? 'data_management.php' : 'expense_list.php';
    header('Location: ../views/' . $redirect_url . '?message=' . urlencode('报销单已成功删除。'));
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Deletion failed: " . $e->getMessage());
    $redirect_url = ($_SERVER['REQUEST_METHOD'] === 'POST') ? 'data_management.php' : 'expense_list.php';
    header('Location: ../views/' . $redirect_url . '?error=' . urlencode('删除失败，请稍后再试。'));
    exit;
}