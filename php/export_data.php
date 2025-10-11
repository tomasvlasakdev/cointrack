<?php
session_start();
include_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get user info
    $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data) {
        throw new Exception("User not found");
    }

    // Get transactions
    $stmt = $pdo->prepare("
        SELECT 
            id,
            symbol,
            type AS transaction_type,
            amount AS quantity,
            price AS price_per_unit,
            (amount * price) AS total_value,
            created_at AS transaction_date
        FROM transactions
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Portfolio summary (simple aggregate)
    $stmt = $pdo->prepare("
        SELECT 
            symbol,
            SUM(CASE WHEN type = 'BUY' THEN amount ELSE -amount END) AS total_quantity,
            AVG(CASE WHEN type = 'BUY' THEN price ELSE NULL END) AS avg_buy_price
        FROM transactions
        WHERE user_id = ?
        GROUP BY symbol
        HAVING total_quantity > 0
    ");
    $stmt->execute([$user_id]);
    $portfolio = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Build export data
    $export_data = [
        'export_date' => date('Y-m-d H:i:s'),
        'user' => [
            'username' => $user_data['username'],
            'email' => $user_data['email'],
            'member_since' => $user_data['created_at']
        ],
        'portfolio_summary' => $portfolio,
        'transactions' => $transactions,
        'statistics' => [
            'total_transactions' => count($transactions),
            'assets_owned' => count($portfolio)
        ]
    ];

    // Output JSON
    $json_data = json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="portfolio_export_' . date('Y-m-d') . '.json"');
    header('Content-Length: ' . strlen($json_data));
    echo $json_data;

} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to export data: ' . $e->getMessage()]);
}
?>
