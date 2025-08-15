<?php
$user = $_SESSION['user'] ?? null;
// 获取当前页面的文件名，用于导航高亮显示
$current_page = basename($_SERVER['PHP_SELF']);
if (basename($_SERVER['REQUEST_URI']) === 'index.php' || $_SERVER['REQUEST_URI'] === '/') {
    $current_page = 'index.php';
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>金瑞员工报销系统</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">
                <h1>金瑞员工报销系统</h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <?php if ($user): ?>
                        <?php if ($user['role'] === 'admin'): ?>
                            <li class="<?php echo ($current_page === 'index.php') ? 'active' : ''; ?>">
                                <a href="/index.php">首页</a>
                            </li>
                            <li class="<?php echo ($current_page === 'expense_list.php') ? 'active' : ''; ?>">
                                <a href="/views/expense_list.php">报销单列表</a>
                            </li>
                            <li class="<?php echo ($current_page === 'manage_categories.php') ? 'active' : ''; ?>">
                                <a href="/views/manage_categories.php">费用类别管理</a>
                            </li>
                            <li class="<?php echo ($current_page === 'users.php') ? 'active' : ''; ?>">
                                <a href="/views/users.php">用户管理</a>
                            </li>
                            <li class="<?php echo ($current_page === 'data_management.php') ? 'active' : ''; ?>">
                                <a href="/views/data_management.php">数据管理</a>
                            </li>
                        <?php else: ?>
                            <li class="<?php echo ($current_page === 'expense_list.php' || $current_page === 'submit_expense.php') ? 'active' : ''; ?>">
                                <a href="/views/expense_list.php">我的报销单</a>
                            </li>
                        <?php endif; ?>
                        <li class="<?php echo ($current_page === 'user_profile.php') ? 'active' : ''; ?>">
                            <a href="/views/user_profile.php">个人资料</a>
                        </li>
                        <li>
                            <a href="/actions/logout.php">退出 (<?php echo htmlspecialchars($user['nickname']); ?>)</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>