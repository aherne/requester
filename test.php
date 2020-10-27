<?php

// $ch = curl_init();

// curl_setopt($ch, CURLOPT_URL,            "http://localhost/requester/receiver.php");
// curl_setopt($ch, CURLOPT_POST,           1 );
// curl_setopt($ch, CURLOPT_POSTFIELDS,     file_get_contents("composer.json"));
// curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain'));
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );

// $result=curl_exec ($ch);
// echo $result;
// curl_close($ch);
// die();
require __DIR__ . '/vendor/autoload.php';
try {
    define("RECEIVER_HTTP", "http://localhost/requester/receiver.php");
    define("RECEIVER_HTTPS", "https://www.lucinda-framework.com/public/receiver.php");
    new Lucinda\UnitTest\ConsoleController("unit-tests.xml", "local");
} catch (Exception $e) {
    echo $e->getMessage();
}
