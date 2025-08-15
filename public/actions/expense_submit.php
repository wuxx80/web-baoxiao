<?php
session_start();
require_once '../../config/config.php';

// 检查用户是否登录
if (!isset($_SESSION['user'])) {
    header('Location: ../views/login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$report_id = null;
$error = '';
$message = '';
$total_amount = 0;

// 检查表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 确保 expense_items 数组存在且不为空
    if (empty($_POST['expense_items'])) {
        $error = "请至少添加一个报销项目。";
    } else {
        // 开始事务
        $pdo->beginTransaction();

        try {
            // 1. 插入主报销单，并获取 ID
            $stmt = $pdo->prepare("INSERT INTO expense_reports (user_id, status) VALUES (?, 'pending')");
            $stmt->execute([$user_id]);
            $report_id = $pdo->lastInsertId();

            // 2. 循环处理并插入每个报销项目
            foreach ($_POST['expense_items'] as $item) {
                $category_id = filter_var($item['category_id'], FILTER_VALIDATE_INT);
                $amount = filter_var($item['amount'], FILTER_VALIDATE_FLOAT);
                $date = $item['date'];
                $description = trim($item['description']);
                
                // 验证输入
                if ($category_id === false || $amount === false || empty($date) || empty($description)) {
                    throw new Exception("无效的表单数据，请检查所有字段。");
                }
                
                // 累加总金额
                $total_amount += $amount;

                // 插入报销项目
                $stmt_item = $pdo->prepare("INSERT INTO expense_items (report_id, category_id, amount, date, description) VALUES (?, ?, ?, ?, ?)");
                $stmt_item->execute([$report_id, $category_id, $amount, $date, $description]);
            }
            
            // 3. 更新主报销单的总金额
            $stmt_update = $pdo->prepare("UPDATE expense_reports SET total_amount = ? WHERE id = ?");
            $stmt_update->execute([$total_amount, $report_id]);

            // 提交事务
            $pdo->commit();

            $message = "报销单提交成功！";
            header("Location: ../views/expense_list.php?message=" . urlencode($message));
            exit;

        } catch (Exception $e) {
            // 回滚事务
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