<?php
session_start();
require_once '../../config/config.php';
require_once 'header.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // 修正跳转路径
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$categories = [];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $category_name = trim($_POST['category_name'] ?? '');
        if (!empty($category_name)) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$category_name]);
            $success = "费用类别 '{$category_name}' 添加成功！";
        } else {
            $error = "费用类别名称不能为空。";
        }
    }

    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "数据库操作失败: " . $e->getMessage();
}
?>

<div class="main-content">
    <h2>管理费用类别</h2>

    <?php if ($success): ?>
        <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card add-category-card">
        <h3>添加新的费用类别</h3>
        <form action="manage_categories.php" method="post">
            <div class="form-group">
                <label for="category_name">类别名称</label>
                <input type="text" id="category_name" name="category_name" required>
            </div>
            <button type="submit" class="btn btn-primary">添加类别</button>
        </form>
    </div>

    <div class="card category-list-card">
        <h3>现有费用类别</h3>
        <?php if (empty($categories)): ?>
            <p>目前没有费用类别。</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>名称</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td class="action-buttons-td">
                                <a href="edit_category.php?id=<?php echo htmlspecialchars($category['id']); ?>" class="btn btn-secondary">编辑</a>
                                <button class="btn btn-delete" onclick="deleteCategory(<?php echo htmlspecialchars($category['id']); ?>)">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
    .add-category-card, .category-list-card {
        max-width: 600px;
        margin: 20px auto;
        padding: 30px;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }
    .card h3 {
        text-align: center;
        margin-bottom: 20px;
    }
    .action-buttons-td a, .action-buttons-td button {
        margin-right: 5px;
    }
</style>

<script>
    function deleteCategory(categoryId) {
        if (confirm("确定要删除此费用类别吗？")) {
            fetch('/actions/delete_category.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${categoryId}`
            }).then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    alert('删除失败，请重试。');
                }
            }).catch(error => {
                console.error('Error deleting category:', error);
                alert('删除失败，请检查网络。');
            });
        }
    }
</script>

<?php require_once 'footer.php'; ?>