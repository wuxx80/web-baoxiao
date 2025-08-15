<?php
session_start();
require_once '../../config/config.php';
require_once 'header.php';

// 检查是否为管理员
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: expense_list.php');
    exit;
}

try {
    // 获取所有用户
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("查询用户失败: " . $e->getMessage());
}
?>

<div class="main-content">
    <h2>用户管理</h2>
    <div class="action-buttons">
        <a href="user_add.php" class="btn btn-primary">新增用户</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>用户名</th>
                <th>昵称</th>
                <th>角色</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['nickname']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <a href="user_edit.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-secondary">编辑</a>
                        <a href="../actions/user_delete.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-danger" onclick="return confirm('确定要删除此用户吗？');">删除</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>