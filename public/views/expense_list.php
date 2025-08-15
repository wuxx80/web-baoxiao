<?php
session_start();
require_once '../../config/config.php';
require_once 'header.php';

if (!isset($_SESSION['user'])) {
    header('Location: /views/login.php');
    exit;
}

// 在这里定义状态映射数组
$status_map = [
    'pending' => '待处理',
    'approved' => '已批准',
    'rejected' => '已驳回'
];

$user_role = $_SESSION['user']['role'];
$user_id = $_SESSION['user']['id'];

$page_title = '所有报销单';
$status_filter = $_GET['status'] ?? null;
$params = [];
$where_clause = '';

if ($status_filter) {
    $valid_statuses = ['pending', 'approved', 'rejected'];
    if (in_array($status_filter, $valid_statuses)) {
        $where_clause .= " er.status = ? ";
        $params[] = $status_filter;
        $page_title = $status_map[$status_filter] . '报销单';
    }
}

try {
    if ($user_role === 'admin') {
        $sql = "SELECT er.*, u.nickname FROM expense_reports er JOIN users u ON er.user_id = u.id";
        if ($where_clause) {
            $sql .= " WHERE " . $where_clause;
        }
        $sql .= " ORDER BY er.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        // 普通用户只能看自己的报销单
        $sql = "SELECT er.*, u.nickname FROM expense_reports er JOIN users u ON er.user_id = u.id WHERE er.user_id = ?";
        if ($where_clause) {
            $sql .= " AND " . $where_clause;
            $params[] = $user_id;
        } else {
            $params = [$user_id];
        }
        $sql .= " ORDER BY er.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
    $expenses = $stmt->fetchAll();
} catch (PDOException $e) {
    die("查询报销单失败: " . $e->getMessage());
}
?>

<div class="main-content">
    <h2><?php echo htmlspecialchars($page_title); ?></h2>

    <?php if ($user_role !== 'admin'): ?>
    <div class="action-buttons">
        <a href="submit_expense.php" class="btn btn-primary">提交新的报销单</a>
    </div>
    <?php endif; ?>

    <?php if (empty($expenses)): ?>
        <p>没有找到报销单记录。</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>报销人</th>
                    <th>标题</th>
                    <th>金额</th>
                    <th>状态</th>
                    <th>提交日期</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($expense['id']); ?></td>
                        <td><?php echo htmlspecialchars($expense['nickname']); ?></td>
                        <td><?php echo htmlspecialchars($expense['title']); ?></td>
                        <td>¥<?php echo number_format($expense['amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($expense['status']); ?>">
                                <?php echo htmlspecialchars($status_map[$expense['status']]); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($expense['created_at']); ?></td>
                        <td class="action-buttons-td">
                            <a href="/views/expense_view.php?id=<?php echo htmlspecialchars($expense['id']); ?>" class="btn btn-secondary">查看</a>
                            <?php if ($user_role === 'admin' && $expense['status'] === 'pending'): ?>
                                <a href="/actions/expense_approve.php?id=<?php echo htmlspecialchars($expense['id']); ?>" class="btn btn-approve">批准</a>
                                <button class="btn btn-reject" onclick="handleReject(<?php echo htmlspecialchars($expense['id']); ?>)">驳回</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
    /* 增加一个样式来让操作按钮排列更美观 */
    .action-buttons-td a, .action-buttons-td button {
        margin-right: 5px;
    }
</style>

<?php require_once 'footer.php'; ?>

<script>
    const userRole = "<?php echo htmlspecialchars($user_role); ?>";
    const statusMap = <?php echo json_encode($status_map); ?>;

    function handleReject(id) {
        const reason = prompt("请输入驳回原因:");
        if (reason) {
            fetch('/actions/expense_reject.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&reason=${encodeURIComponent(reason)}`
            }).then(response => {
                if (response.ok) {
                    updateExpenseList();
                } else {
                    alert('驳回失败，请重试。');
                }
            }).catch(error => {
                console.error('Error rejecting expense:', error);
                alert('驳回失败，请检查网络。');
            });
        }
    }

    function updateExpenseList() {
        fetch('/api/get_updates.php?type=expense_list')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }
                const tbody = document.querySelector('table tbody');
                let newTbodyContent = '';

                data.expenses.forEach(expense => {
                    const statusText = statusMap[expense.status] || expense.status;
                    let actionLinks = `<a href="/views/expense_view.php?id=${expense.id}" class="btn btn-secondary">查看</a>`;
                    
                    if (userRole === 'admin' && expense.status === 'pending') {
                        actionLinks += `
                            <a href="/actions/expense_approve.php?id=${expense.id}" class="btn btn-approve">批准</a>
                            <button class="btn btn-reject" onclick="handleReject(${expense.id})">驳回</button>
                        `;
                    }

                    newTbodyContent += `
                        <tr>
                            <td>${expense.id}</td>
                            <td>${expense.nickname}</td>
                            <td>${expense.title}</td>
                            <td>¥${parseFloat(expense.amount).toFixed(2)}</td>
                            <td><span class="status-badge status-${expense.status}">${statusText}</span></td>
                            <td>${expense.created_at}</td>
                            <td class="action-buttons-td">${actionLinks}</td>
                        </tr>
                    `;
                });
                tbody.innerHTML = newTbodyContent;
            })
            .catch(error => {
                console.error('Error fetching expense list data:', error);
            });
    }

    setInterval(updateExpenseList, 5000);
    updateExpenseList();
</script>