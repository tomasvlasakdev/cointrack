<?php
session_start();
include_once 'sidebar.php';
include_once __DIR__ . '/../config.php';
include_once 'functions.php';

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['email'])) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Location: /PCv/cointrack_2/index.php");
    exit();
}

$transactions_per_page = 10;
$current_page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($current_page - 1) * $transactions_per_page;

$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
$stmt_count->execute([$user_id]);
$total_transactions = $stmt_count->fetchColumn();
$total_pages = $transactions_per_page > 0 ? ceil($total_transactions / $transactions_per_page) : 1;

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $transactions_per_page, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$coins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate HTML table
$table = "<table class='transactions-table'>";
$table .= "<tr class='first_tr'><th>Type</th><th>Symbol</th><th>Amount</th><th>Value</th><th></th></tr>";

if (empty($coins)) {
    $table .= "<tr><td colspan='5'>No transactions found.</td></tr>";
} else {
    foreach ($coins as $coin) {
        $type = $coin['type'] == 'BUY' ? 'Receive' : 'Send';
        $symbol = strtoupper($coin['symbol']);
        $amount = number_format($coin['amount'], 8);
        $value = number_format($coin['amount'] * $coin['price'], 2);
        $transaction_id = $coin['id'];

        $table .= "<tr>";
        $table .= "<td>" . htmlspecialchars($type) . "</td>";
        $table .= "<td>" . htmlspecialchars($symbol) . "</td>";
        $table .= "<td>" . htmlspecialchars($amount) . "</td>";
        $table .= "<td>$" . htmlspecialchars($value) . "</td>";
        $table .= "<td class='actions'>";
        $table .= "<div class='dropdown_dots'>";
        $table .= "<span class='dots'>⋮</span>";
        $table .= "<div class='dropdown-content'>";
        $table .= "<a href='edit_transaction.php?id=$transaction_id&action=change_type'>Change Type</a>";
        $table .= "<a href='edit_transaction.php?id=$transaction_id&action=edit'>Edit Transaction</a>";
        $table .= "<a href='delete_transaction.php?id=$transaction_id' onclick='return confirm(\"Are you sure you want to delete this transaction?\")'>Delete Transaction</a>";
        $table .= "</div>";
        $table .= "</div>";
        $table .= "</td>";
        $table .= "</tr>";
    }
}
$table .= "</table>";


//  Pagination Links Generation 
$pagination_html = '';
if ($total_pages > 1) {
    $pagination_html .= '<div class="pagination">';
    $base_url = "transactions.php?";

    // Previous button
    if ($current_page > 1) {
        $pagination_html .= "<a href='{$base_url}page=" . ($current_page - 1) . "'>&laquo; Previous</a>";
    }

    // Page numbers (displaying around current page)
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);

    if ($start > 1) {
        $pagination_html .= "<a href='{$base_url}page=1'>1</a>";
        if ($start > 2)
            $pagination_html .= "<span>...</span>";
    }

    for ($i = $start; $i <= $end; $i++) {
        $active_class = $i == $current_page ? 'active' : '';
        $pagination_html .= "<a class='{$active_class}' href='{$base_url}page={$i}'>{$i}</a>";
    }

    if ($end < $total_pages) {
        if ($end < $total_pages - 1)
            $pagination_html .= "<span>...</span>";
        $pagination_html .= "<a href='{$base_url}page={$total_pages}'>{$total_pages}</a>";
    }

    // Next button
    if ($current_page < $total_pages) {
        $pagination_html .= "<a href='{$base_url}page=" . ($current_page + 1) . "'>Next &raquo;</a>";
    }
    $pagination_html .= '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions | CoinTrack</title>
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
                        <h1>Transactions</h1>
                        <div class="add-transaction"><a href="add_transaction.php">+ Add transaction</a></div>
                    </div>
                    <div class="transactions-section">
                        <div class="transactions-table"><?php echo $table; ?> </div>
                        <div> <?php echo $pagination_html; ?> </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script>
        document.querySelectorAll('.dots').forEach(dots => {
            dots.addEventListener('click', (e) => {
                const dropdown = dots.nextElementSibling;
                const isOpen = dropdown.classList.contains('show');
                document.querySelectorAll('.dropdown-content.show').forEach(menu => menu.classList.remove('show'));
                if (!isOpen) {
                    dropdown.classList.add('show');
                }
            });
        });

        document.addEventListener('click', (e) => {
            if (!e.target.classList.contains('dots')) {
                document.querySelectorAll('.dropdown-content.show').forEach(menu => menu.classList.remove('show'));
            }
        });
    </script>
</body>

</html>