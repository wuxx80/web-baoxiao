<?php
require 'header.php';

if ($_SESSION['user']['role'] != 'admin') {
    header('Location: expense_list.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: categories.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    die("分类不存在。");
}
?>
<div class="form-window-container">
    <div class="card">
        <div class="card-header">
            <h2>编辑分类：<?php echo htmlspecialchars($category['name']); ?></h2>
        </div>
        <div class="card-body">
            <form action="../actions/category_edit.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                <div class="form-group">
                    <label for="name">分类名称</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn">保存修改</button>
                    <a href="categories.php" class="btn btn-secondary">返回</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>