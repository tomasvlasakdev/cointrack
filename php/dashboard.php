<?php
session_start();
include_once __DIR__ . '/../config.php';
include_once 'functions.php';
include_once 'sidebar.php';

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}


$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ?");
$stmt->execute([$user_id]);
$coins = $stmt->fetchAll(PDO::FETCH_ASSOC);

$portfolio = [];

foreach ($coins as $coin) {
    $symbol = $coin['symbol'];

    // Create portfolio for the symbol if not exists
    if (!isset($portfolio[$symbol])) {
        $portfolio[$symbol] = [
            'amount' => 0,
            'invested' => 0,
            'avg_price' => 0,
            'current_price' => 0,
            'value_now' => 0,
            'profit_loss' => 0
        ];
    }

    if ($coin['type'] == 'BUY') {
        // Add amount and invested value for buy transactions
        $portfolio[$symbol]['amount'] += $coin['amount'];
        $invested_this_transaction = $coin['amount'] * $coin['price'];
        $portfolio[$symbol]['invested'] += $invested_this_transaction;

        $portfolio[$symbol]['avg_price'] = $portfolio[$symbol]['invested'] / $portfolio[$symbol]['amount']; // Recalculate average price
    } else {
        // Sell transaction
        $portfolio[$symbol]['amount'] -= $coin['amount']; // Calculate realized profit/loss for sold amount (but don't add to profit_loss for unrealized)

        $realized_profit_loss = $coin['amount'] * ($coin['price'] - $portfolio[$symbol]['avg_price']); // Update invested amount (remove based on avg_price)

        $portfolio[$symbol]['invested'] -= $coin['amount'] * $portfolio[$symbol]['avg_price'];

    }
}

$total_value = 0;
$total_invested = 0;
$total_return = 0;
$unique_symbols = array_unique(array_column($coins, 'symbol'));
$js_portfolio = [];
$symbol_to_id = [];

foreach ($unique_symbols as $symbol) {
    $id = get_coin_id($symbol);
    if ($id) {
        $symbol_to_id[$symbol] = $id;
    }
}

$valid_ids = array_values($symbol_to_id);
$prices = get_current_prices($valid_ids);

foreach ($unique_symbols as $symbol) {
    if (isset($symbol_to_id[$symbol])) {
        $id = $symbol_to_id[$symbol];
        $price = $prices[$id] ?? 0;
        if (isset($portfolio[$symbol])) {
            $portfolio[$symbol]['current_price'] = $price;
            $portfolio[$symbol]['value_now'] = $portfolio[$symbol]['amount'] * $price;
            // Calculate unrealized profit/loss for remaining holdings
            $portfolio[$symbol]['profit_loss'] = $portfolio[$symbol]['value_now'] - $portfolio[$symbol]['invested'];
            $total_value += $portfolio[$symbol]['value_now'];
            $total_invested += $portfolio[$symbol]['invested'];
            $total_return += $portfolio[$symbol]['profit_loss'];
            if ($portfolio[$symbol]['amount'] > 0) {
                $js_portfolio[$symbol] = ['id' => $id, 'amount' => $portfolio[$symbol]['amount']];
            }
        }
    }
}

$percent = ($total_invested > 0) ? ($total_return / $total_invested) * 100 : 0;
$sign = $total_return > 0 ? '+' : ($total_return < 0 ? '-' : '');
$total_return_str = $sign . '$' . number_format(abs($total_return), 2) . ' (' . $sign . number_format(abs($percent), 2) . '%)';
$total_return_color = $total_return < 0 ? 'style="color: #ff4d4d;"' : '';

// Sum of unrealized returns is the same as total_return
$sum_unrealized = $total_return;
$sum_unrealized_str = $sign . '$' . number_format(abs($sum_unrealized), 2) . ' (' . $sign . number_format(abs($percent), 2) . '%)';
$sum_unrealized_color = $sum_unrealized < 0 ? 'style="color: #ff4d4d;"' : '';



// Generate HTML table
$table = "<table class='portfolio-table' style='text-align: left;'>";
$table .= "<tr>";
$table .= "<th><p>Symbol</p></th>";
$table .= "<th>
    <div class=\"header-content\">
        <p>Amount</p> 
        <span class=\"arrow\">
            <span class=\"arrow-up\"><svg width=\"10\" height=\"6\" viewBox=\"0 0 10 6\"><polygon points=\"0,6 5,0 10,6\" fill=\"#4d4848ff\"/></svg></span>
            <span class=\"arrow-down\"><svg width=\"10\" height=\"6\" viewBox=\"0 0 10 6\"><polygon points=\"0,0 5,6 10,0\" fill=\"#4d4848ff\"/></svg></span>
        </span>
    </div>
</th>";
$table .= "<th>
    <div class=\"header-content\">
        <p>Average Buying Price</p> 
        <span class=\"arrow\">
            <span class=\"arrow-up\"><svg width=\"10\" height=\"6\" viewBox=\"0 0 10 6\"><polygon points=\"0,6 5,0 10,6\" fill=\"#4d4848ff\"/></svg></span>
            <span class=\"arrow-down\"><svg width=\"10\" height=\"6\" viewBox=\"0 0 10 6\"><polygon points=\"0,0 5,6 10,0\" fill=\"#4d4848ff\"/></svg></span>
        </span>
    </div>
</th>";
$table .= "<th>
    <div class=\"header-content\">
        <p>Current Price</p> 
        <span class=\"arrow\">
            <span class=\"arrow-up\"><svg width=\"10\" height=\"6\" viewBox=\"0 0 10 6\"><polygon points=\"0,6 5,0 10,6\" fill=\"#4d4848ff\"/></svg></span>
            <span class=\"arrow-down\"><svg width=\"10\" height=\"6\" viewBox=\"0 0 10 6\"><polygon points=\"0,0 5,6 10,0\" fill=\"#4d4848ff\"/></svg></span>
        </span>
    </div>
</th>";
$table .= "<th>
    <div class=\"header-content\">
        <p>Current value</p> 
        <span class=\"arrow\">
            <span class=\"arrow-up\"><svg width=\"10\" height=\"6\" viewBox=\"0 0 10 6\"><polygon points=\"0,6 5,0 10,6\" fill=\"#4d4848ff\"/></svg></span>
            <span class=\"arrow-down\"><svg width=\"10\" height=\"6\" viewBox=\"0 0 10 6\"><polygon points=\"0,0 5,6 10,0\" fill=\"#4d4848ff\"/></svg></span>
        </span>
    </div>
</th>";
$table .= "<th><p>Unrealized Return</p></th>";
$table .= "</tr>";

foreach ($portfolio as $symbol => $data) {
    $pl_percent = ($data['invested'] > 0) ? ($data['profit_loss'] / $data['invested']) * 100 : 0;
    $pl_sign = $data['profit_loss'] > 0 ? '+' : ($data['profit_loss'] < 0 ? '-' : '');
    $pl_color = $data['profit_loss'] < 0 ? 'style="color: red;"' : ($data['profit_loss'] > 0 ? 'style="color: var(--accent-green);"' : '');
    $table .= "<tr>";
    $table .= "<td>" . htmlspecialchars(strtoupper($symbol)) . "</td>";
    $table .= "<td>" . htmlspecialchars(number_format($data['amount'], 8)) . "</td>";
    $table .= "<td>" . htmlspecialchars(number_format($data['avg_price'], 2)) . "</td>";
    $table .= "<td>" . htmlspecialchars(number_format($data['current_price'], 2)) . "</td>";
    $table .= "<td>" . htmlspecialchars(number_format($data['value_now'], 2)) . "</td>";
    $table .= "<td $pl_color>" . htmlspecialchars(number_format($data['profit_loss'], 2)) . " ($pl_sign" . number_format(abs($pl_percent), 2) . "%)</td>";
    $table .= "</tr>";
}
$table .= "</table>";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio | CoinTrack</title>
    <script>
        (function() {
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <main class="main-container">
        <?php sidebar(); ?>
        <section class="content">
            <div class="center">
                <div class="middle">
                    <div class="portfolio-header">
                        <h1>Portfolio Overview</h1>
                        <div class="portfolio-summary">
                            <div class="portfolio-total">
                                <span class="label">Total Value</span>
                                <span class="value">$<?= number_format($total_value, 2) ?></span>
                            </div>
                            <div class="portfolio-profit">
                                <span class="label">Change In Portfolio Value</span>
                                <span class="value" id="total-profit"></span>
                            </div>
                            <div class="portfolio-unrealized">
                                <span class="label">Total Unrealized Returns</span>
                                <span class="value" <?= $sum_unrealized_color ?>><?= $sum_unrealized_str ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="portfolio-graph-section">
                        <div class="range-selector">
                            <label for="range-selector">Range:</label>
                            <select id="range-selector">
                                <option value="1">1D</option>
                                <option value="7">1W</option>
                                <option value="30" selected>1M</option>
                                <option value="365">1Y</option>
                            </select>
                            <button id="refresh-btn">↻ Refresh</button>
                        </div>
                        <small id="last-update" style="color:#888;display:block;margin-top:4px;"></small>
                        <canvas id="portfolio-chart" width="800" height="400"></canvas>
                    </div>
                    <div class="assets-section">
                        <div class="assets-info">Your assets</div>
                        <div class="add-assets">
                            <div id="portfolio-table"><?php echo $table; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script>
        document.getElementById('range-selector').addEventListener('change', e => {
            localStorage.setItem('portfolioRange', e.target.value);
            fetchData(e.target.value);
        });
        document.getElementById('refresh-btn').addEventListener('click', () => {
            const range = document.getElementById('range-selector').value;
            localStorage.removeItem(`portfolio_chart_data_${range}`);
            fetchData(range);
        });
        document.getElementById('range-selector').value = localStorage.getItem('portfolioRange') || '30';
        fetchData(document.getElementById('range-selector').value);

        const portfolioData = <?= json_encode($js_portfolio) ?>;

        const userHasHoldings = Object.values(portfolioData).some(p => p.amount > 0); // Check if the user has any coins with a positive amount

        async function fetchData(days) {
            const chartContainer = document.getElementById('portfolio-chart');
            const ctx = chartContainer.getContext('2d');

            // Clear the canvas for loading message
            ctx.clearRect(0, 0, chartContainer.width, chartContainer.height);
            ctx.font = '16px Anta';
            ctx.fillStyle = '#888';
            ctx.textAlign = 'center';
            ctx.fillText('Loading portfolio data...', chartContainer.width / 2, chartContainer.height / 2);

            const cacheKey = `portfolio_chart_data_${days}`;

            // Try to load data from Local Storage 
            const cached = localStorage.getItem(cacheKey);
            let fromCache = false;
            if (cached) {
                try {
                    const parsed = JSON.parse(cached);
                    renderChart(parsed.labels, parsed.values, true, parsed.timestamp);
                    fromCache = true;
                } catch { }
            }

            // Load data from server
            try {
                const res = await fetch(`fetch_portfolio_data.php?days=${days}`);
                if (!res.ok) throw new Error('Network');
                const data = await res.json();

                // Check if the user has any assets OR if the server returned an empty array for a non-empty portfolio
                if (Object.keys(data).length === 0 && !userHasHoldings) {
                    renderNoAssetsMessage();
                    return;
                }

                const values = [];
                const first = Object.values(data)[0];
                if (Object.keys(data).length === 0) {

                    // If it reaches here, it means userHasHoldings is true, but API returned empty. 
                    // If the API call succeeded but returned no data, it means no chart data is available.

                    if (!fromCache) { // If not rendering from cache, display an error
                        renderApiErrorMessage('Data for assets not available or API response was empty.');
                    }
                    return;
                }

                const numPoints = first.prices.length;
                for (let i = 0; i < numPoints; i++) {
                    let total = 0;
                    for (let sym in data) {
                        if (portfolioData[sym] && portfolioData[sym].amount > 0) {
                            const prices = data[sym].prices;
                            const price = prices[i][1];
                            total += price * portfolioData[sym].amount;
                        }
                    }
                    values.push(total);
                }

                const labels = first.prices.map(p => new Date(p[0]).toLocaleDateString());

                // Save to Local Storage
                const timestamp = Date.now();
                localStorage.setItem(cacheKey, JSON.stringify({ labels, values, timestamp }));

                renderChart(labels, values, false, timestamp);

            } catch (err) {
                const middleX = chartContainer.width / 2;
                const middleY = chartContainer.height / 2;

                if (fromCache) {
                    // If the fetch fails but the cache has already rendered the chart, 
                    // do nothing or just log the error. The cache is already displayed.
                    console.error('Server fetch failed, using cached data.', err);
                } else if (userHasHoldings) {
                    // FIX: If the user HAS holdings but the API call failed AND there's NO cache.
                    renderApiErrorMessage('⚠️ Error loading live data. Please try again later or refresh. (API Failure)');
                } else {
                    // If the user does NOT have holdings and the fetch failed.
                    renderNoAssetsMessage();
                }
            }
        }

        function renderApiErrorMessage(message) {
            const chartContainer = document.getElementById('portfolio-chart');
            const ctx = chartContainer.getContext('2d');

            if (window.myChart) window.myChart.destroy();

            ctx.clearRect(0, 0, chartContainer.width, chartContainer.height);
            ctx.font = '20px Anta';
            ctx.fillStyle = 'red'; 
            ctx.textAlign = 'center';

            const middleX = chartContainer.width / 2;
            const middleY = chartContainer.height / 2;

            ctx.fillText(message, middleX, middleY);

            document.getElementById('last-update').textContent = '';
        }

        function renderNoAssetsMessage() {
            const chartContainer = document.getElementById('portfolio-chart');
            const ctx = chartContainer.getContext('2d');

            if (window.myChart) window.myChart.destroy();

            ctx.clearRect(0, 0, chartContainer.width, chartContainer.height);
            ctx.font = '20px Anta';
            ctx.fillStyle = '#f0f0f0';
            ctx.textAlign = 'center';

            const middleX = chartContainer.width / 2;
            const middleY = chartContainer.height / 2;

            ctx.fillText('🚀 Your portfolio is empty!', middleX, middleY - 20);

            ctx.font = '16px Arial';
            ctx.fillStyle = '#888';
            ctx.fillText('Add a transaction to see your portfolio history.', middleX, middleY + 20);

            document.getElementById('last-update').textContent = '';
            const totalProfitEl = document.getElementById('total-profit');
            totalProfitEl.textContent = '$0.00 (0.00%)';
            totalProfitEl.className = 'value';
        }

        function renderChart(labels, data, fromCache = false, timestamp = Date.now()) {
            const ctx = document.getElementById('portfolio-chart').getContext('2d');
            if (window.myChart) window.myChart.destroy();

            const color = fromCache ? 'gray' : 'blue';
            const label = fromCache ? 'Portfolio Value (cached)' : 'Portfolio Value';

            // Check if there is actual data to draw (to prevent showing error of not displaying the graph)
            if (data.length === 0 || data.every(v => v === 0)) {
                renderNoAssetsMessage();
                return;
            }

            window.myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label,
                        data,
                        borderColor: color,
                        fill: false
                    }]
                },
                options: {
                    scales: { y: { beginAtZero: false } },
                    plugins: {
                        legend: { labels: { color: '#ddd' } }
                    }
                }

            });

            // Update last-update
            const lastUpdateEl = document.getElementById('last-update');
            const dateStr = new Date(timestamp).toLocaleString();
            lastUpdateEl.textContent = fromCache ? `Cached data from ${dateStr}` : `Updated ${dateStr}`;

            // Update total-profit
            const totalProfitEl = document.getElementById('total-profit');
            if (data.length > 1 && data[0] !== 0) {
                const profit = data[data.length - 1] - data[0];
                const percent = (profit / data[0]) * 100;
                const displayProfit = Math.abs(profit).toFixed(2);
                const displayPercent = Math.abs(percent).toFixed(2);
                totalProfitEl.textContent = `${profit < 0 ? '-' : ''}$${displayProfit} (${percent < 0 ? '-' : ''}${displayPercent}%)`;
                totalProfitEl.className = profit < 0 ? 'negative value' : 'positive value';
            } else {
                totalProfitEl.textContent = '$0.00 (0.00%)';
                totalProfitEl.className = '';
            }
        }

        // Sorting functionality
        const tableContainer = document.getElementById('portfolio-table');
        const table = tableContainer.querySelector('table');
        const headers = table.querySelectorAll('th');

        headers.forEach((header, col) => {
            const upArrow = header.querySelector('.arrow-up');
            const downArrow = header.querySelector('.arrow-down');

            if (upArrow) upArrow.addEventListener('click', () => sortTable(col, 'asc'));
            if (downArrow) downArrow.addEventListener('click', () => sortTable(col, 'desc'));
        });

        function sortTable(col, dir) {
            const tbody = table.querySelector('tbody') || table;
            const rows = Array.from(tbody.querySelectorAll('tr')).slice(1);

            rows.sort((a, b) => {
                let A = a.cells[col].innerText.trim();
                let B = b.cells[col].innerText.trim();

                // If it is unrealized return, which is column n. 5, extract only the first number
                if (col === 5) {
                    const matchA = A.match(/-?[\d.,]+/);
                    const matchB = B.match(/-?[\d.,]+/);
                    const numA = matchA ? parseFloat(matchA[0].replace(/,/g, '')) : 0;
                    const numB = matchB ? parseFloat(matchB[0].replace(/,/g, '')) : 0;
                    return dir === 'asc' ? numA - numB : numB - numA;
                }

                // The rest is extracted normally
                A = A.replace(/[^\d.-]/g, '');
                B = B.replace(/[^\d.-]/g, '');
                const numA = parseFloat(A) || 0;
                const numB = parseFloat(B) || 0;
                return dir === 'asc' ? numA - numB : numB - numA;
            });

            rows.forEach(row => tbody.appendChild(row));
        }


        // Initial sort by Unrealized Return descending (highest to lowest)
        sortTable(5, 'desc');

    </script>
</body>

</html>