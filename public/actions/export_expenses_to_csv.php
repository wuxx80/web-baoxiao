<?php
session_start();
require_once '../../config/config.php';

// 检查用户是否登录，并且角色为管理员
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit;
}

try {
    // 查询所有报销单及其包含的费用项目
    $stmt = $pdo->prepare("
        SELECT
            er.id AS report_id,
            u.nickname AS user_nickname,
            er.total_amount,
            er.status AS report_status,
            er.created_at,
            e.id AS expense_id,
            c.name AS category_name,
            e.amount,
            e.expense_date,
            e.approver_project,
            e.description
        FROM
            expense_reports er
        JOIN
            users u ON er.user_id = u.id
        JOIN
            expenses e ON e.report_id = er.id
        JOIN
            categories c ON e.category_id = c.id
        ORDER BY
            er.created_at DESC, e.id ASC
    ");
    $stmt->execute();
    $expenses = $stmt->fetchAll();

    if (empty($expenses)) {
        header('Location: ../views/expense_list.php?error=没有可导出的报销数据。');
        exit;
    }

    // 设置 HTTP 头，强制浏览器下载文件
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="expense_reports_' . date('Y-m-d') . '.csv"');

    // 打开一个文件句柄，将输出写入其中
    $output = fopen('php://output', 'w');

    // 写入 CSV 文件的 BOM 头，以解决中文乱码问题
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // 写入 CSV 文件的标题行
    fputcsv($output, [
        '报销单ID',
        '用户昵称',
        '总金额',
        '报销单状态',
        '提交时间',
        '费用项目ID',
        '费用分类',
        '费用金额',
        '费用日期',
        '审批人/项目',
        '费用描述'
    ]);

    // 写入报销数据行
    foreach ($expenses as $row) {
        fputcsv($output, [
            $row['report_id'],
            $row['user_nickname'],
            $row['total_amount'],
            $row['report_status'],
            $row['created_at'],
            $row['expense_id'],
            $row['category_name'],
            $row['amount'],
            $row['expense_date'],
            $row['approver_project'],
            $row['description']
        ]);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    header('Location: ../views/expense_list.php?error=数据导出失败，请稍后再试。');
    exit;
}