<?php
session_start();
require_once '../../config/config.php';

// 检查用户是否登录
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require 'header.php';

$user = $_SESSION['user'];
$success_message = $_GET['message'] ?? '';
$error_message = $_GET['error'] ?? '';
?>

<div class="form-window-container">
    <div class="card">
        <div class="card-header">
            <h2>个人资料</h2>
        </div>
        <div class="card-body">
            <?php if ($success_message): ?>
                <div class="alert success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form action="../actions/user_update_profile_action.php" method="POST">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="nickname">昵称</label>
                    <input type="text" id="nickname" name="nickname" value="<?php echo htmlspecialchars($user['nickname']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="new_password">新密码</label>
                    <input type="password" id="new_password" name="new_password" placeholder="留空则不修改密码">
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn">更新资料</button>
                    <a href="expense_list.php" class="btn btn-secondary">返回</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>