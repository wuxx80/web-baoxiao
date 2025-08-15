<?php
session_start();
require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_role = $_SESSION['user']['role'];
$user_id = $_SESSION['user']['id'];
$type = $_GET['type'] ?? '';

$response = [];

try {
    if ($type === 'dashboard_stats' && $user_role === 'admin') {
        // 返回管理员的仪表盘统计数据
        $response = [
            'pending_count' => $pdo->query("SELECT COUNT(*) FROM expense_reports WHERE status = 'pending'")->fetchColumn(),
            'approved_count' => $pdo->query("SELECT COUNT(*) FROM expense_reports WHERE status = 'approved'")->fetchColumn(),
            'rejected_count' => $pdo->query("SELECT COUNT(*) FROM expense_reports WHERE status = 'rejected'")->fetchColumn(),
            'total_count' => $pdo->query("SELECT COUNT(*) FROM expense_reports")->fetchColumn(),
        ];
    } elseif ($type === 'expense_list') {
        // 返回报销单列表，明确列出所有需要的字段
        $sql = "SELECT er.id, er.user_id, er.title, er.amount, er.status, er.created_at, u.nickname FROM expense_reports er JOIN users u ON er.user_id = u.id";

        if ($user_role === 'admin') {
            $stmt = $pdo->query($sql . " ORDER BY er.created_at DESC");
        } else {
            // 普通用户只能看自己的报销单
            $sql .= " WHERE er.user_id = ?";
            $stmt = $pdo->prepare($sql . " ORDER BY er.created_at DESC");
            $stmt->execute([$user_id]);
        }
        $response['expenses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        http_response_code(400);
        $response['error'] = 'Invalid request type or permissions.';
    }
} catch (PDOException $e) {
    http_response_code(500);
    $response['error'] = 'Database query failed: ' . $e->getMessage();
}

echo json_encode($response);
?>