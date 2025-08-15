<?php
session_start();
require_once '../../config/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require 'header.php';

$report_id = $_GET['id'] ?? null;
$current_user_id = $_SESSION['user']['id'];
$user_role = $_SESSION['user']['role'];

if (!$report_id) {
    header('Location: expense_list.php?error=未指定报销单ID。');
    exit;
}

try {
    // 获取报销单的头部信息
    $stmt = $pdo->prepare("
        SELECT
            er.id,
            u.nickname,
            er.total_amount,
            er.status,
            er.created_at
        FROM
            expense_reports er
        JOIN
            users u ON er.user_id = u.id
        WHERE
            er.id = ?
    ");
    $stmt->execute([$report_id]);
    $report = $stmt->fetch();

    if (!$report) {
        header('Location: expense_list.php?error=未找到指定的报销单。');
        exit;
    }

    // 检查权限：管理员可以查看所有，普通用户只能查看自己的
    if ($user_role !== 'admin' && $report['user_id'] !== $current_user_id) {
        header('Location: expense_list.php?error=您无权查看此报销单。');
        exit;
    }

    // 获取报销单中的具体费用项目
    $stmt = $pdo->prepare("
        SELECT
            e.id,
            c.name AS category_name,
            e.amount,
            e.expense_date,
            e.approver_project,
            e.description
        FROM
            expenses e
        JOIN
            categories c ON e.category_id = c.id
        WHERE
            e.report_id = ?
    ");
    $stmt->execute([$report_id]);
    $expense_items = $stmt->fetchAll();

} catch (PDOException $e) {
    echo "查询失败: " . $e->getMessage();
    exit;
}
?>

<div class="main-content">
    <div class="actions-header">
        <h2>报销单详情 #<?php echo htmlspecialchars($report['id']); ?></h2>
        <a href="expense_list.php" class="btn btn-secondary">返回列表</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>报销单信息</h4>
        </div>
        <div class="card-body">
            <p><strong>提交用户:</strong> <?php echo htmlspecialchars($report['nickname']); ?></p>
            <p><strong>总金额:</strong> <?php echo htmlspecialchars(number_format($report['total_amount'], 2)); ?></p>
            <p><strong>状态:</strong>
                <?php
                $status = $report['status'];
                $status_class = '';
                if ($status === 'pending') {
                    $status_class = 'status-pending';
                } elseif ($status === 'approved') {
                    $status_class = 'status-approved';
                } elseif ($status === 'rejected') {
                    $status_class = 'status-rejected';
                }
                ?>
                <span class="status-badge <?php echo $status_class; ?>">
                    <?php
                    switch ($status) {
                        case 'pending':
                            echo '待处理';
                            break;
                        case 'approved':
                            echo '已批准';
                            break;
                        case 'rejected':
                            echo '已驳回';
                            break;
                    }
                    ?>
                </span>
            </p>
            <p><strong>提交时间:</strong> <?php echo htmlspecialchars($report['created_at']); ?></p>
        </div>
    </div>

    <div class="data-table-container" style="margin-top: 20px;">
        <h4>费用项目列表</h4>
        <?php if (!empty($expense_items)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>分类</th>
                        <th>金额</th>
                        <th>报销日期</th>
                        <th>审批人/项目</th>
                        <th>描述</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expense_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['id']); ?></td>
                            <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                            <td><?php echo htmlspecialchars(number_format($item['amount'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($item['expense_date']); ?></td>
                            <td><?php echo htmlspecialchars($item['approver_project']); ?></td>
                            <td><?php echo htmlspecialchars($item['description']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>该报销单中没有费用项目。</p>
        <?php endif; ?>
    </div>
</div>

<?php require 'footer.php'; ?>