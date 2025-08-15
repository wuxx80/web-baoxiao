<?php
session_start();
require_once '../config/config.php';

// 如果用户未登录，重定向到登录页面
if (!isset($_SESSION['user'])) {
    header('Location: /views/login.php');
    exit;
}

$user_role = $_SESSION['user']['role'];

// 如果是普通用户，直接重定向到报销单列表
if ($user_role !== 'admin') {
    header('Location: /views/expense_list.php');
    exit;
}

// 登录且是管理员，显示数据概览作为首页
require 'views/header.php';

try {
    // 获取报销单状态统计
    $pending_count = $pdo->query("SELECT COUNT(*) FROM expense_reports WHERE status = 'pending'")->fetchColumn();
    $approved_count = $pdo->query("SELECT COUNT(*) FROM expense_reports WHERE status = 'approved'")->fetchColumn();
    $rejected_count = $pdo->query("SELECT COUNT(*) FROM expense_reports WHERE status = 'rejected'")->fetchColumn();
    $total_count = $pdo->query("SELECT COUNT(*) FROM expense_reports")->fetchColumn();

} catch (PDOException $e) {
    echo "查询失败: " . $e->getMessage();
    exit;
}
?>

<div class="main-content">
    <h2>数据概览</h2>
    <div class="dashboard-grid">
        <a href="/views/expense_list.php?status=pending" class="dashboard-card pending-card">
            <div class="card-icon"><img src="assets/images/pending_icon.png" alt="待处理"></div>
            <div class="card-content">
                <h3>待处理报销单</h3>
                <p class="dashboard-number"><?php echo htmlspecialchars($pending_count); ?></p>
            </div>
        </a>

        <a href="/views/expense_list.php?status=approved" class="dashboard-card approved-card">
            <div class="card-icon"><img src="assets/images/approved_icon.png" alt="已批准"></div>
            <div class="card-content">
                <h3>已批准报销单</h3>
                <p class="dashboard-number"><?php echo htmlspecialchars($approved_count); ?></p>
            </div>
        </a>

        <a href="/views/expense_list.php?status=rejected" class="dashboard-card rejected-card">
            <div class="card-icon"><img src="assets/images/rejected_icon.png" alt="已驳回"></div>
            <div class="card-content">
                <h3>已驳回报销单</h3>
                <p class="dashboard-number"><?php echo htmlspecialchars($rejected_count); ?></p>
            </div>
        </a>

        <a href="/views/expense_list.php" class="dashboard-card total-card">
            <div class="card-icon"><img src="assets/images/total_icon.png" alt="总数"></div>
            <div class="card-content">
                <h3>报销单总数</h3>
                <p class="dashboard-number"><?php echo htmlspecialchars($total_count); ?></p>
            </div>
        </a>
    </div>
</div>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin-top: 30px;
    }
    @media (max-width: 768px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
    .dashboard-card {
        display: flex;
        align-items: center;
        background-color: #ffffff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-decoration: none; /* 移除链接下划线 */
        color: inherit; /* 继承父元素颜色 */
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.12);
    }
    .card-icon {
        width: 60px;
        height: 60px;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 50%;
        margin-right: 20px;
        flex-shrink: 0;
    }
    .card-icon img {
        width: 36px;
        height: 36px;
    }
    .card-content {
        text-align: left;
    }
    .dashboard-card h3 {
        color: #666;
        font-size: 1.1em;
        margin: 0;
        font-weight: 500;
    }
    .dashboard-number {
        font-size: 2.8em;
        font-weight: 700;
        margin: 5px 0 0;
        line-height: 1.2;
    }
    .pending-card .card-icon { background-color: #fff3e0; }
    .pending-card .dashboard-number { color: #f9a825; }
    .approved-card .card-icon { background-color: #e8f5e9; }
    .approved-card .dashboard-number { color: #43a047; }
    .rejected-card .card-icon { background-color: #ffebee; }
    .rejected-card .dashboard-number { color: #e53935; }
    .total-card .card-icon { background-color: #e3f2fd; }
    .total-card .dashboard-number { color: #1e88e5; }
</style>
<script>
    function updateDashboard() {
        fetch('/api/get_updates.php?type=dashboard_stats')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }
                document.querySelector('.pending-card .dashboard-number').textContent = data.pending_count;
                document.querySelector('.approved-card .dashboard-number').textContent = data.approved_count;
                document.querySelector('.rejected-card .dashboard-number').textContent = data.rejected_count;
                document.querySelector('.total-card .dashboard-number').textContent = data.total_count;
            })
            .catch(error => {
                console.error('Error fetching dashboard data:', error);
            });
    }

    // 每5秒更新一次
    setInterval(updateDashboard, 5000);
    // 页面加载后立即执行一次
    updateDashboard();
</script>

<?php require 'views/footer.php'; ?>