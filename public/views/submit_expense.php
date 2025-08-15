<?php
session_start();
require_once '../../config/config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header('Location: login.php');
    exit;
}

require 'header.php';

$error = $_GET['error'] ?? '';
$message = $_GET['message'] ?? '';

// 获取报销分类
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "查询分类失败: " . $e->getMessage();
    exit;
}

// 获取当前日期，用于预填写日期输入框
$current_date = date('Y-m-d');
?>

<div class="form-window-container">
    <div class="card">
        <div class="card-header">
            <h2>创建报销单</h2>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($message): ?>
                <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form action="../actions/submit_expense_action.php" method="post" id="expense-form">
                <div class="form-group">
                    <label for="expense_date">报销日期</label>
                    <input type="date" name="expense_date" id="expense_date" value="<?php echo $current_date; ?>" required>
                </div>

                <div class="form-group">
                    <label for="approver_project">被报销人</label>
                    <input type="text" name="approver_project" id="approver_project" required>
                    <small class="form-text text-muted">备注：报销在谁的项目中</small>
                </div>

                <hr>

                <h4>费用明细</h4>
                <p>请在需要报销的分类中填写金额，不需要的可以留空。</p>
                <div class="expense-categories-grid">
                    <?php foreach ($categories as $category): ?>
                        <div class="form-group">
                            <label for="category_<?php echo htmlspecialchars($category['id']); ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </label>
                            <input type="number" step="0.01"
                                name="amount[<?php echo htmlspecialchars($category['id']); ?>]"
                                id="category_<?php echo htmlspecialchars($category['id']); ?>"
                                placeholder="请输入金额">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-group">
                    <label for="description">总描述</label>
                    <textarea name="description" id="description" rows="3"></textarea>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn">提交报销单</button>
                    <a href="expense_list.php" class="btn btn-secondary">返回</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .expense-categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        margin-top: 20px;
        margin-bottom: 20px;
    }
</style>

<?php require 'footer.php'; ?>