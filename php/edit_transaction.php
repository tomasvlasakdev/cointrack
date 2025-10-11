<?php
session_start();
include_once 'sidebar.php';
include_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /PCv/cointrack_2/index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id = intval($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$tx = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tx) {
    die("Transaction not found or access denied.");
}

// Change type without redirecting to edit_transaction.php
if ($action === 'change_type') {
    $newType = $tx['type'] === 'BUY' ? 'SELL' : 'BUY';
    $update = $pdo->prepare("UPDATE transactions SET type = ? WHERE id = ? AND user_id = ?");
    $update->execute([$newType, $id, $user_id]);
    header("Location: transactions.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $symbol = strtoupper(trim($_POST['symbol']));
    $amount = floatval($_POST['amount']);
    $price = floatval($_POST['price']);

    $update = $pdo->prepare("UPDATE transactions SET type = ?, symbol = ?, amount = ?, price = ? WHERE id = ? AND user_id = ?");
    $update->execute([$type, $symbol, $amount, $price, $id, $user_id]);

    header("Location: transactions.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Transaction</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            }
        })();
    </script>
    <link rel="icon" href="../favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anta&display=swap" rel="stylesheet">
    <style>

    </style>
</head>

<body>
    <main class="main-container">
        <?php sidebar() ?>
        <section class="content">
            <div class="center">
                <div class="middle">
                    <div class="transactions-header">
                        <h1>Edit Transaction</h1>
                    </div>

                    <div class="form-container_edit">
                        <form method="POST">
                            <label>Type</label>
                            <select class="select-input_edit" name="type" required>
                                <option value="BUY" <?= $tx['type'] == 'BUY' ? 'selected' : '' ?>>RECIEVE</option>
                                <option value="SELL" <?= $tx['type'] == 'SELL' ? 'selected' : '' ?>>SEND</option>
                            </select>

                            <label>Symbol</label>
                            <input class="select-input_edit" type="text" name="symbol"
                                value="<?= htmlspecialchars($tx['symbol']) ?>" required>

                            <label>Amount</label>
                            <input class="select-input_edit" type="number" step="0.00000001" name="amount"
                                value="<?= htmlspecialchars($tx['amount']) ?>" required>

                            <label>Price (USD)</label>
                            <input class="select-input_edit" type="number" step="0.01" name="price"
                                value="<?= htmlspecialchars($tx['price']) ?>" required>

                            <button class="button_edit" type="submit">Save Changes</button>
                        </form>
                        <a href="transactions.php" class="back">← Back</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>

</html>