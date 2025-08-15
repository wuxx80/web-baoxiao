<?php
require 'header.php';

if ($_SESSION['user']['role'] != 'admin') {
    header('Location: expense_list.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: users.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("用户不存在。");
}
?>
<div class="form-window-container">
    <div class="card">
        <div class="card-header">
            <h2>编辑用户：<?php echo htmlspecialchars($user['nickname']); ?></h2>
        </div>
        <div class="card-body">
            <form action="../actions/user_edit.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="nickname">昵称</label>
                    <input type="text" name="nickname" id="nickname" value="<?php echo htmlspecialchars($user['nickname']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="role">角色</label>
                    <select name="role" id="role">
                        <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>普通用户</option>
                        <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>管理员</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">新密码 <span class="note">(不修改请留空)</span></label>
                    <input type="password" name="password" id="password">
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn">保存修改</button>
                    <a href="users.php" class="btn btn-secondary">返回</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>