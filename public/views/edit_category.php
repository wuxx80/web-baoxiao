<?php
session_start();
require_once '../../config/config.php';
require_once 'header.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /views/login.php');
    exit;
}

$category = null;
$error = '';
$success = '';

if (isset($_GET['id'])) {
    $category_id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM expense_categories WHERE id = ?");
        $stmt->execute([$category_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            $error = "找不到该费用类别。";
        }
    } catch (PDOException $e) {
        $error = "查询数据库失败：" . $e->getMessage();
    }
} else {
    $error = "缺少类别ID。";
}
?>

<div class="main-content">
    <?php if ($error): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php else: ?>
        <div class="card edit-category-card">
            <h2>编辑费用类别</h2>
            <form action="/actions/update_category.php" method="post">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($category['id']); ?>">
                <div class="form-group">
                    <label for="name">类别名称</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">保存修改</button>
                <a href="manage_categories.php" class="btn btn-secondary">取消</a>
            </form>
        </div>
    <?php endif; ?>
</div>

<style>
    .edit-category-card {
        max-width: 600px;
        margin: 20px auto;
        padding: 30px;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        text-align: center;
    }
    .edit-category-card h2 {
        margin-bottom: 20px;
    }
    .edit-category-card .btn {
        margin: 0 5px;
    }
</style>

<?php require_once 'footer.php'; ?>