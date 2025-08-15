<?php
session_start();
require_once '../../config/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require 'header.php';

try {
    // 获取报销单状态统计
    $pending_count = $pdo->query("SELECT COUNT(*) FROM expense_reports WHERE status = 'pending'")->fetchColumn();
    $approved_count = $pdo->query("SELECT COUNT(*) FROM expense_reports WHERE status = 'approved'")->fetchColumn();
    $rejected_count = $pdo->query("SELECT COUNT(*) FROM expense_reports WHERE status = 'rejected'")->fetchColumn();
    $total_count = $pdo->query("SELECT COUNT(*) FROM expense_reports")->fetchColumn();

} catch (PDOException $e) {
    // 为了防止页面崩溃，这里只打印错误信息
    echo "查询失败: " . $e->getMessage();
    exit;
}
?>

<div class="main-content">
    <h2>数据概览</h2>
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>待处理报销单</h3>
            <p class="dashboard-number pending"><?php echo htmlspecialchars($pending_count); ?></p>
        </div>
        <div class="dashboard-card">
            <h3>已批准报销单</h3>
            <p class="dashboard-number approved"><?php echo htmlspecialchars($approved_count); ?></p>
        </div>
        <div class="dashboard-card">
            <h3>已驳回报销单</h3>
            <p class="dashboard-number rejected"><?php echo htmlspecialchars($rejected_count); ?></p>
        </div>
        <div class="dashboard-card">
            <h3>报销单总数</h3>
            <p class="dashboard-number total"><?php echo htmlspecialchars($total_count); ?></p>
        </div>
    </div>
</div>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    .dashboard-card {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
    }
    .dashboard-card h3 {
        color: #555;
        font-size: 1.2em;
        margin-bottom: 10px;
    }
    .dashboard-number {
        font-size: 2.5em;
        font-weight: bold;
        margin: 0;
    }
    .dashboard-number.pending { color: #f0ad4e; }
    .dashboard-number.approved { color: #5cb85c; }
    .dashboard-number.rejected { color: #d9534f; }
    .dashboard-number.total { color: #337ab7; }
</style>

<?php require 'footer.php'; ?>