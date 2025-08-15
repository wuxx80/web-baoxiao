<?php
session_start();
require_once '../../config/config.php';
require_once 'header.php';

// 临时代码，用于显示所有 PHP 错误。如果修复后仍有问题，请保留此代码并告诉我错误。
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if (!isset($_SESSION['user'])) {
    header('Location: /views/login.php');
    exit;
}

$user_role = $_SESSION['user']['role'];
$user_id = $_SESSION['user']['id'];

$expense = null;
$error = '';

if (isset($_GET['id'])) {
    $expense_id = $_GET['id'];
    try {
        // 获取报销单详情及用户信息
        $stmt = $pdo->prepare("SELECT er.*, u.nickname FROM expense_reports er JOIN users u ON er.user_id = u.id WHERE er.id = ?");
        $stmt->execute([$expense_id]);
        $expense = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$expense) {
            $error = "找不到该报销单。";
        } elseif ($user_role !== 'admin' && $expense['user_id'] !== $user_id) {
            // 如果不是管理员，且报销单不属于当前用户，则拒绝访问
            $error = "您没有权限查看此报销单。";
            $expense = null;
        }

    } catch (PDOException $e) {
        $error = "查询失败: " . $e->getMessage();
    }
} else {
    $error = "缺少报销单ID。";
}

// 定义状态映射数组
$status_map = [
    'pending' => '待处理',
    'approved' => '已批准',
    'rejected' => '已驳回'
];
?>

<div class="main-content">
    <?php if ($error): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($expense): ?>
        <div class="expense-details-card">
            <h2>报销单详情 #<?php echo htmlspecialchars($expense['id']); ?></h2>
            <div class="detail-row">
                <strong>提交人：</strong>
                <span><?php echo htmlspecialchars($expense['nickname']); ?></span>
            </div>
            <div class="detail-row">
                <strong>标题：</strong>
                <span><?php echo htmlspecialchars($expense['title']); ?></span>
            </div>
            <div class="detail-row">
                <strong>金额：</strong>
                <span>¥<?php echo number_format($expense['amount'], 2); ?></span>
            </div>
            <div class="detail-row">
                <strong>提交日期：</strong>
                <span><?php echo htmlspecialchars($expense['created_at']); ?></span>
            </div>
            <div class="detail-row">
                <strong>状态：</strong>
                <span class="status-badge status-<?php echo htmlspecialchars($expense['status']); ?>">
                    <?php echo htmlspecialchars($status_map[$expense['status']]); ?>
                </span>
            </div>
            <?php if ($expense['status'] === 'rejected' && !empty($expense['rejection_reason'])): ?>
                <div class="detail-row">
                    <strong>驳回原因：</strong>
                    <p class="rejection-reason-text"><?php echo nl2br(htmlspecialchars($expense['rejection_reason'])); ?></p>
                </div>
            <?php endif; ?>
            <div class="detail-row">
                <strong>报销内容：</strong>
                <p><?php echo nl2br(htmlspecialchars($expense['description'])); ?></p>
            </div>
            <div class="detail-actions">
                <a href="/views/expense_list.php" class="btn btn-secondary">返回列表</a>
                <?php if ($user_role === 'admin' && $expense['status'] === 'pending'): ?>
                    <a href="/actions/expense_approve.php?id=<?php echo htmlspecialchars($expense['id']); ?>" class="btn btn-approve">批准</a>
                    <button class="btn btn-reject" onclick="handleReject(<?php echo htmlspecialchars($expense['id']); ?>)">驳回</button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .expense-details-card {
        max-width: 600px;
        margin: 20px auto;
        padding: 30px;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }
    .expense-details-card h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #333;
        font-size: 1.8em;
        border-bottom: 2px solid #eee;
        padding-bottom: 15px;
    }
    .detail-row {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    .detail-row strong {
        width: 120px;
        font-weight: 600;
        color: #555;
        flex-shrink: 0;
    }
    .detail-row span, .detail-row p {
        flex-grow: 1;
        margin: 0;
        color: #333;
    }
    .detail-row p {
        background-color: #f9f9f9;
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 15px;
        line-height: 1.6;
        white-space: pre-wrap; /* 保留换行符 */
    }
    .rejection-reason-text {
        font-style: italic;
        color: #e53935;
        border-left: 3px solid #e53935;
        padding-left: 10px;
    }
    .detail-actions {
        margin-top: 30px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
</style>

<script>
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
                    window.location.href = '/views/expense_list.php';
                } else {
                    alert('驳回失败，请重试。');
                }
            }).catch(error => {
                console.error('Error rejecting expense:', error);
                alert('驳回失败，请检查网络。');
            });
        }
    }
</script>

<?php require_once 'footer.php'; ?>