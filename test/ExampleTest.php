<?php

include_once __DIR__ . '/../DvaService.php';

try {
    $price = (new DvaService('abc.com'))->getPrice();
    foreach ($price as $value) {
        echo $value['platform'] . "\t : " . $value['price'] . " \t".  $value['currency'] . "\n";
    }
} catch (ErrorException $e) {
    echo $e->getMessage();
    exit(1);
}