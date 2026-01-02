<?php

require '../php-binance-api.php';

// @see home_directory_config.php
// use config from ~/.confg/jaggedsoft/php-binance-api.json
$api = new Binance\API("<testnet api key>","<testnet secret>", true);

// Place a LIMIT order using the testnet
$quantity = 1000;
$price = 0.0005;
$order = $api->buy("BNBBTC", $quantity, $price);
print_r($order);

