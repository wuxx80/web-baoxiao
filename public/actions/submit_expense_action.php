<?php
session_start();
require_once '../../config/config.php';

// 检查用户是否登录
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: ../views/login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$report_id = null;
$error = '';
$total_amount = 0;

// 检查表单是否通过 POST 方法提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据
    $expense_date = $_POST['expense_date'] ?? null;
    $approver_project = trim($_POST['approver_project'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $amounts = $_POST['amount'] ?? [];
    
    // 从session中获取收款人（payee）
    $payee = $_SESSION['user']['nickname'] ?? '';

    // 验证基本数据
    if (empty($amounts) || empty($expense_date) || empty($approver_project) || empty($payee)) {
        $error = "请填写所有必填字段。";
    } else {
        // 开始数据库事务
        $pdo->beginTransaction();
        try {
            // 1. 循环处理每个报销项目，计算总金额
            $valid_items_count = 0;
            foreach ($amounts as $category_id => $amount) {
                if (!empty($amount) && $amount > 0) {
                    $total_amount += $amount;
                    $valid_items_count++;
                }
            }

            // 检查是否有实际提交的报销项目
            if ($valid_items_count === 0) {
                throw new Exception("请至少填写一个报销项目的金额。");
            }

            // 2. 插入主报销单到 expense_reports 表
            // 注意：我们直接将计算出的总金额插入到 total_amount 和 amount 两个字段，以同时满足可能存在的两个字段。
            $stmt = $pdo->prepare("INSERT INTO expense_reports (user_id, payee, total_amount, amount, status, created_at, description) VALUES (?, ?, ?, ?, 'pending', NOW(), ?)");
            $stmt->execute([$user_id, $payee, $total_amount, $total_amount, $description]);
            $report_id = $pdo->lastInsertId();

            // 3. 循环处理每个报销项目，并插入到 expenses 表
            foreach ($amounts as $category_id => $amount) {
                if (!empty($amount) && $amount > 0) {
                    $stmt_item = $pdo->prepare("INSERT INTO expenses (report_id, category_id, amount, expense_date, approver_project, description) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_item->execute([$report_id, $category_id, $amount, $expense_date, $approver_project, $description]);
                }
            }
            
            // 提交事务
            $pdo->commit();
            $message = "报销单提交成功！";
            header("Location: ../views/expense_list.php?message=" . urlencode($message));
            exit;

        } catch (Exception $e) {
            // 如果有任何错误，回滚事务
            $pdo->rollBack();
            $error = "提交失败: " . $e->getMessage();
        }
    }
}

// 如果提交失败，将错误信息传递回表单页面
if ($error) {
    header("Location: ../views/submit_expense.php?error=" . urlencode($error));
    exit;
}
?>