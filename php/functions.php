<?php
function is_post_request(): bool {
    return (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST');
}

function is_get_request(): bool {
    return (strtoupper($_SERVER['REQUEST_METHOD']) === 'GET');
}


function get_coin_id($symbol) {
    $low_symbol = strtolower($symbol);
    $cache_dir = __DIR__ . '/cache/';
    $cache_file = $cache_dir . 'coin_id_' . md5($low_symbol) . '.json';

    // Create cache directory if it doesn't exist
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }

    $id = null;
    if (file_exists($cache_file)) {
        $id = file_get_contents($cache_file);
    }

    $age = file_exists($cache_file) ? time() - filemtime($cache_file) : 999999;

    if ($age < 86400 && $id !== null) {
        return $id;
    }

    $url = 'https://api.coingecko.com/api/v3/search?query=' . urlencode($low_symbol);
    $response = @file_get_contents($url);
    if ($response === false) {
        return $id; // Fall back to last known
    }

    $data = json_decode($response, true);
    $coins = $data['coins'] ?? [];

    if (empty($coins)) {
        return $id; // Fall back
    }

    usort($coins, function($a, $b) {
        if ($a['market_cap_rank'] === null) return 1;
        if ($b['market_cap_rank'] === null) return -1;
        return $a['market_cap_rank'] <=> $b['market_cap_rank'];
    });

    $new_id = $coins[0]['id'];
    file_put_contents($cache_file, $new_id);
    return $new_id;
}

function get_current_prices(array $ids) {
    $ids = array_unique($ids);
    $prices = [];
    $cache_dir = __DIR__ . '/cache/';

    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }

    // Load existing caches
    foreach ($ids as $id) {
        $cache_file = $cache_dir . 'price_' . $id . '.json';
        if (file_exists($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            $prices[$id] = $data['usd'] ?? 0;
        } else {
            $prices[$id] = 0;
        }
    }

    // Collect IDs needing refresh
    $to_fetch = [];
    foreach ($ids as $id) {
        $cache_file = $cache_dir . 'price_' . $id . '.json';
        if (!file_exists($cache_file) || (time() - filemtime($cache_file) > 60)) {
            $to_fetch[] = $id;
        }
    }

    if (!empty($to_fetch)) {
        $url = 'https://api.coingecko.com/api/v3/simple/price?ids=' . urlencode(implode(',', $to_fetch)) . '&vs_currencies=usd';
        $response = @file_get_contents($url);
        if ($response !== false) {
            $data = json_decode($response, true);
            foreach ($to_fetch as $id) {
                if (isset($data[$id]['usd']) && $data[$id]['usd'] > 0) {
                    $cache_data = ['usd' => $data[$id]['usd']];
                    file_put_contents($cache_dir . 'price_' . $id . '.json', json_encode($cache_data));
                    $prices[$id] = $data[$id]['usd'];
                }
            }
        }
        // If fetch fails, keep existing prices from cache
    }

    return $prices;
}

function get_market_chart($id, $days) {
    $cache_dir = __DIR__ . '/cache/';
    $cache_file = $cache_dir . "chart_{$id}_{$days}.json";

    if (!is_dir($cache_dir)) mkdir($cache_dir, 0777, true);

    // Use saved data if newer than 1 hour
    if (file_exists($cache_file) && time() - filemtime($cache_file) < 3600) {
        return json_decode(file_get_contents($cache_file), true);
    }

    $url = "https://api.coingecko.com/api/v3/coins/$id/market_chart?vs_currency=usd&days=$days";
    $response = @file_get_contents($url);
    if ($response === false) {
        // Return last data, if they exist
        if (file_exists($cache_file)) {
            return json_decode(file_get_contents($cache_file), true);
        }
        return null;
    }

    file_put_contents($cache_file, $response);
    return json_decode($response, true);
}

