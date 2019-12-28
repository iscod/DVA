<?php

include_once 'DvaService.php';

try {
    $price = (new DvaService('abc.com'))->getPrice();
    foreach ($price as $value) {
        echo $value['currency'] . "\t : " . $value['price'] . "\n";
    }
} catch (ErrorException $e) {
    echo $e->getMessage();
    exit(1);
}