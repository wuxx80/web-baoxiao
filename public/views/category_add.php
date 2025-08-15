<?php
require 'header.php';

if ($_SESSION['user']['role'] != 'admin') {
    header('Location: expense_list.php');
    exit;
}
?>
<div class="form-window-container">
    <div class="card">
        <div class="card-header">
            <h2>新增分类</h2>
        </div>
        <div class="card-body">
            <form action="../actions/category_add.php" method="POST">
                <div class="form-group">
                    <label for="name">分类名称</label>
                    <input type="text" name="name" id="name" required>
                </div>
                <div class="action-buttons">
                    <button type="submit" class="btn">添加分类</button>
                    <a href="categories.php" class="btn btn-secondary">返回</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>