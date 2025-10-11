<?php
session_start();
include_once 'sidebar.php';
include_once __DIR__ . '/../config.php';

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['email'])) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Location: /PCv/cointrack_2/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $symbol = strtoupper($_POST['symbol']);
    $amount = floatval($_POST['amount']);
    $price = floatval($_POST['price']);
    $date = date('Y-m-d H:i:s');

    if ($amount > 0 && $price > 0) {
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, symbol, amount, price, type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $symbol, $amount, $price, $type]);
        header("Location: transactions.php");
        exit();
    } else {
        $error = "Invalid amount or price.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Transaction | CoinTrack</title>
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            }
        })();
    </script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" href="../favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anta&display=swap" rel="stylesheet">
</head>

<body>
    <main class="main-container">

        <?php sidebar(); ?>

        <section class="content">
            <div class="center">
                <div class="middle">
                    <div class="transactions-header">
                        <h1>Add Transaction</h1>
                    </div>

                    <div class="form-container_add">
                        <div>
                        </div>
                        <?php if (isset($error)): ?>
                            <div class="error"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="type">Type</label>
                                <select id="type" name="type" required>
                                    <option value="BUY">Receive</option>
                                    <option value="SELL">Send</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="symbol">Symbol</label>
                                <input type="text" id="symbol" name="symbol" required placeholder="e.g., BTC">
                            </div>
                            <div class="form-group">
                                <label for="amount">Amount</label>
                                <input type="number" id="amount" name="amount" step="0.00000001" required
                                    placeholder="0.0">
                            </div>
                            <div class="form-group">
                                <label for="price">Price per Unit (USD)</label>
                                <input type="number" id="price" name="price" step="0.01" required placeholder="0.0">
                            </div>
                            <div class="form-buttons">
                                <button type="submit" class="save-btn">Save</button>
                                <a href="transactions.php"><button type="button" class="cancel-btn">Cancel</button></a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

    </main>
</body>

</html>