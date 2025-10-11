<?php
session_start();
include_once __DIR__ . '/../config.php';
include_once 'functions.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$days = $_GET['days'] ?? '30';
$cache_dir = __DIR__ . '/cache/';

if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0777, true);
}

$cache_file = $cache_dir . 'portfolio_data_' . md5($user_id . '_' . $days) . '.json';

// Check user transactions
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ?");
$stmt->execute([$user_id]);
$coins = $stmt->fetchAll(PDO::FETCH_ASSOC);

$portfolio = [];

foreach ($coins as $coin) {
    $symbol = $coin['symbol'];
    if (!isset($portfolio[$symbol])) {
        $portfolio[$symbol] = [
            'amount' => 0,
            'invested' => 0,
            'avg_price' => 0
        ];
    }

    if ($coin['type'] === 'BUY') {
        $portfolio[$symbol]['amount'] += $coin['amount'];
        $portfolio[$symbol]['invested'] += $coin['amount'] * $coin['price'];
        $portfolio[$symbol]['avg_price'] = $portfolio[$symbol]['invested'] / $portfolio[$symbol]['amount'];
    } elseif ($coin['type'] === 'SELL') {
        $portfolio[$symbol]['amount'] -= $coin['amount'];
        $portfolio[$symbol]['invested'] -= $coin['amount'] * $portfolio[$symbol]['avg_price'];
    }
}

// Build API input
$js_portfolio = [];
foreach ($portfolio as $symbol => $data) {
    if ($data['amount'] > 0) {
        $id = get_coin_id($symbol);
        if ($id) {
            $js_portfolio[$symbol] = [
                'id' => $id,
                'amount' => $data['amount']
            ];
        }
    }
}

header('Content-Type: application/json');

// Do not use cache if newer transactions exist
$last_tx_stmt = $pdo->prepare("SELECT MAX(created_at) AS last_tx FROM transactions WHERE user_id = ?");
$last_tx_stmt->execute([$user_id]);
$last_tx_time = strtotime($last_tx_stmt->fetchColumn() ?? '1970-01-01');

if (
    file_exists($cache_file) &&
    time() - filemtime($cache_file) < 600 && // cache < 10 min
    filemtime($cache_file) > $last_tx_time   // user hasn't added new tx since cache
) {
    echo file_get_contents($cache_file);
    exit;
}

$result = [];
foreach ($js_portfolio as $sym => $d) {
    $url = "https://api.coingecko.com/api/v3/coins/{$d['id']}/market_chart?vs_currency=usd&days={$days}";
    $json = @file_get_contents($url);
    if ($json === false) continue;
    $result[$sym] = json_decode($json, true);
}

file_put_contents($cache_file, json_encode($result));
echo json_encode($result);
