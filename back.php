<?php
$symbols = ['BTC', 'ETH', 'BNB', 'DOGE', 'XRP', 'LTC', 'SOL', 'TRUMP', 'ADA', 'TRX', 'SUI', 'LINK'];
$intervals = [
    '1h' => 24,  // 24h
    '4h' => 42,  // 7d
    '1d' => 30   // 30d
];

$apiBase = 'https://api.binance.com/api/v3/';
$data = [];

// Fetch current prices
$pricesRaw = @file_get_contents($apiBase . 'ticker/price');
$prices = json_decode($pricesRaw, true);

// Fetch 24hr stats
$statsRaw = @file_get_contents($apiBase . 'ticker/24hr');
$stats = json_decode($statsRaw, true);

// Format quick lookup tables
$priceMap = [];
if (is_array($prices)) {
    foreach ($prices as $item) {
        $priceMap[$item['symbol']] = $item['price'];
    }
}

$statsMap = [];
if (is_array($stats)) {
    foreach ($stats as $item) {
        $statsMap[$item['symbol']] = [
            'highPrice' => $item['highPrice'],
            'lowPrice' => $item['lowPrice']
        ];
    }
}

// Fetch klines in parallel
function fetchKlinesMulti($symbols, $intervals) {
    $multi = curl_multi_init();
    $handles = [];
    $results = [];

    foreach ($symbols as $symbol) {
        $fullSymbol = $symbol . 'EUR';

        foreach ($intervals as $interval => $limit) {
            $url = "https://api.binance.com/api/v3/klines?symbol=$fullSymbol&interval=$interval&limit=$limit";
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false, // Add this if SSL issues
            ]);
            $key = "$symbol:$interval";
            $handles[$key] = $ch;
            curl_multi_add_handle($multi, $ch);
        }
    }

    do {
        curl_multi_exec($multi, $active);
        curl_multi_select($multi);
    } while ($active);

    foreach ($handles as $key => $ch) {
        $response = curl_multi_getcontent($ch);
        [$symbol, $interval] = explode(':', $key);
        $decoded = json_decode($response, true);

        if (is_array($decoded) && !empty($decoded)) {
            $results[$symbol][$interval] = array_map(function ($kline) {
                return [
                    // Convert Binance timestamp (milliseconds) to ISO 8601 format
                    'timestamp' => date('c', $kline[0] / 1000),
                    'price' => floatval($kline[4]) // Close price, convert to float
                ];
            }, $decoded);
        } else {
            $results[$symbol][$interval] = [];
            // Log error for debugging
            error_log("Failed to fetch klines for $symbol:$interval - Response: " . substr($response, 0, 200));
        }

        curl_multi_remove_handle($multi, $ch);
        curl_close($ch);
    }

    curl_multi_close($multi);
    return $results;
}

// Run fast parallel fetch
$klinesData = fetchKlinesMulti($symbols, $intervals);

// Assemble final data response
foreach ($symbols as $symbol) {
    $fullSymbol = $symbol . 'EUR';
    $data[$symbol] = [
        'price' => isset($priceMap[$fullSymbol]) ? floatval($priceMap[$fullSymbol]) : null,
        'highPrice' => isset($statsMap[$fullSymbol]['highPrice']) ? floatval($statsMap[$fullSymbol]['highPrice']) : null,
        'lowPrice' => isset($statsMap[$fullSymbol]['lowPrice']) ? floatval($statsMap[$fullSymbol]['lowPrice']) : null,
        'prices' => $klinesData[$symbol]['1h'] ?? [],
        'weeklyPrices' => $klinesData[$symbol]['4h'] ?? [],
        'monthlyPrices' => $klinesData[$symbol]['1d'] ?? []
    ];
}

// Add error handling
if (empty($data)) {
    $data = ['error' => 'Failed to fetch cryptocurrency data'];
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Add CORS if needed
echo json_encode($data, JSON_PRETTY_PRINT);
?>