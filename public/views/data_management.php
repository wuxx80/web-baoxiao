<?php
session_start();
require_once '../../config/config.php';
require 'header.php';

// 只有管理员才能访问此页面
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';

// --- 数据概览部分的代码 START ---
$stats = [];
$status_map = [];
$category_breakdown = [];

try {
    // 1. 获取报销单总数和总金额
    $stmt = $pdo->query("SELECT COUNT(*) as total_reports, SUM(amount) as total_amount FROM expense_reports");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. 获取不同状态的报销单统计
    $stmt = $pdo->query("SELECT status, COUNT(*) as count, SUM(amount) as amount FROM expense_reports GROUP BY status");
    $status_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 格式化状态数据以便于显示
    foreach ($status_stats as $status) {
        $status_map[$status['status']] = [
            'count' => $status['count'],
            'amount' => $status['amount']
        ];
    }

    // 3. 获取按费用类别划分的金额总计
    $stmt = $pdo->query("SELECT c.name as category_name, SUM(r.amount) as total_amount
                         FROM expense_reports r
                         JOIN expense_categories c ON r.category_id = c.id
                         GROUP BY c.name
                         ORDER BY total_amount DESC");
    $category_breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // 如果查询失败，仅设置错误信息，不中断页面
    $error = "数据概览加载失败: " . $e->getMessage();
}
// --- 数据概览部分的代码 END ---


// --- 报销单列表部分的代码 START ---
// 初始化查询条件
$selected_user_id = $_GET['user_id'] ?? '';
$selected_status = $_GET['status'] ?? '';

try {
    // 获取所有用户，用于筛选下拉列表
    $stmt_users = $pdo->query("SELECT id, nickname FROM users ORDER BY nickname ASC");
    $users = $stmt_users->fetchAll();

    // 构建查询语句
    $sql = "
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
        WHERE 1=1
    ";
    $params = [];

    if (!empty($selected_user_id)) {
        $sql .= " AND er.user_id = ?";
        $params[] = $selected_user_id;
    }

    if (!empty($selected_status)) {
        $sql .= " AND er.status = ?";
        $params[] = $selected_status;
    }

    $sql .= " ORDER BY er.created_at DESC";

    $stmt_reports = $pdo->prepare($sql);
    $stmt_reports->execute($params);
    $reports = $stmt_reports->fetchAll();

} catch (PDOException $e) {
    echo "报销单列表查询失败: " . $e->getMessage();
    exit;
}
// --- 报销单列表部分的代码 END ---
?>

<div class="main-content">
    <h2>数据管理</h2>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="stats-container">
            <div class="stat-box">
                <h3>总报销金额</h3>
                <p class="stat-value">¥ <?php echo number_format($stats['total_amount'] ?? 0, 2); ?></p>
            </div>
            <div class="stat-box">
                <h3>总报销单数</h3>
                <p class="stat-value"><?php echo number_format($stats['total_reports'] ?? 0); ?></p>
            </div>
        </div>

        <div class="status-summary-card card">
            <div class="card-header">
                <h3>按状态统计</h3>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>状态</th>
                            <th>报销单数</th>
                            <th>总金额</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>待处理</td>
                            <td><?php echo number_format($status_map['pending']['count'] ?? 0); ?></td>
                            <td>¥ <?php echo number_format($status_map['pending']['amount'] ?? 0, 2); ?></td>
                        </tr>
                        <tr>
                            <td>已批准</td>
                            <td><?php echo number_format($status_map['approved']['count'] ?? 0); ?></td>
                            <td>¥ <?php echo number_format($status_map['approved']['amount'] ?? 0, 2); ?></td>
                        </tr>
                        <tr>
                            <td>已驳回</td>
                            <td><?php echo number_format($status_map['rejected']['count'] ?? 0); ?></td>
                            <td>¥ <?php echo number_format($status_map['rejected']['amount'] ?? 0, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="category-breakdown-card card">
            <div class="card-header">
                <h3>按费用类别统计</h3>
            </div>
            <div class="card-body">
                <?php if (empty($category_breakdown)): ?>
                    <p>暂无费用类别数据。</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>费用类别</th>
                                <th>总金额</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($category_breakdown as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                    <td>¥ <?php echo number_format($item['total_amount'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        </div>
    
    <hr> <div class="actions-header">
        <h2>报销单列表</h2>
        <div>
            <a href="expense_list.php" class="btn btn-secondary">返回报销单列表</a>
        </div>
    </div>
    
    <?php if ($message): ?>
        <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body">
            <form action="data_management.php" method="get">
                <div class="form-group-inline">
                    <div class="form-group">
                        <label for="user_id">提交人</label>
                        <select name="user_id" id="user_id" class="form-control">
                            <option value="">所有用户</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo htmlspecialchars($user['id']); ?>"
                                    <?php echo ($selected_user_id == $user['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['nickname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">状态</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">所有状态</option>
                            <option value="pending" <?php echo ($selected_status === 'pending') ? 'selected' : ''; ?>>待处理</option>
                            <option value="approved" <?php echo ($selected_status === 'approved') ? 'selected' : ''; ?>>已批准</option>
                            <option value="rejected" <?php echo ($selected_status === 'rejected') ? 'selected' : ''; ?>>已驳回</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="align-self: flex-end;">查询</button>
                    <a href="data_management.php" class="btn btn-secondary" style="align-self: flex-end;">重置</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="data-table-container">
        <?php if (!empty($reports)): ?>
            <form action="../actions/expense_delete.php" method="post" onsubmit="return confirm('确定要删除选中的报销单吗？');">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>报销单ID</th>
                            <th>提交用户</th>
                            <th>总金额</th>
                            <th>状态</th>
                            <th>提交时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td><input type="checkbox" name="report_ids[]" value="<?php echo htmlspecialchars($report['id']); ?>"></td>
                                <td><?php echo htmlspecialchars($report['id']); ?></td>
                                <td><?php echo htmlspecialchars($report['nickname']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($report['total_amount'], 2)); ?></td>
                                <td>
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
                                </td>
                                <td><?php echo htmlspecialchars($report['created_at']); ?></td>
                                <td class="action-buttons-cell">
                                    <a href="expense_detail.php?id=<?php echo $report['id']; ?>" class="btn btn-info btn-sm">查看</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top: 15px;">
                    <button type="submit" class="btn btn-danger">批量删除</button>
                </div>
            </form>
        <?php else: ?>
            <p>没有找到任何报销单记录。</p>
        <?php endif; ?>
    </div>
</div>

<style>
    .form-group-inline {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    .form-group-inline .form-group {
        margin-bottom: 0;
    }
    /* 以下是为数据概览新增的样式 */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
        text-align: center;
    }
    .stat-box {
        background-color: #ffffff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .stat-box h3 {
        margin-top: 0;
        color: #6c757d;
        font-size: 1.1em;
    }
    .stat-value {
        font-size: 2.5em;
        font-weight: bold;
        color: #0d47a1;
        margin: 10px 0 0;
    }
    .status-summary-card table,
    .category-breakdown-card table {
        width: 100%;
        margin-top: 0;
    }
    .status-summary-card .card-body,
    .category-breakdown-card .card-body {
        padding: 1.5rem;
    }
</style>

<script>
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="report_ids[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>

<?php require 'footer.php'; ?>