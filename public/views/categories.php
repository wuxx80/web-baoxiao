<?php
require 'header.php';

if ($_SESSION['user']['role'] != 'admin') {
    header('Location: expense_list.php');
    exit;
}

$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<div class="container">
    <h2>分类管理</h2>
    <?php if ($message): ?>
        <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="action-buttons">
        <a href="category_add.php" class="btn">新增分类</a>
    </div>

    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <h3>所有分类</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>名称</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?php echo $cat['id']; ?></td>
                                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td>
                                    <a href="category_edit.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm">编辑</a>
                                    <a href="../actions/category_delete.php?id=<?php echo $cat['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('确定删除该分类吗？')">删除</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>