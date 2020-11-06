<?php
// to test, current dir must be writable (chmod 777 CURRENT-FOLDER)
require __DIR__ . '/vendor/autoload.php';
try {
    define("RECEIVER_HTTP", "http://localhost/requester/receiver.php");
    define("RECEIVER_HTTPS", "https://www.lucinda-framework.com/public/receiver.php");
    new Lucinda\UnitTest\ConsoleController("unit-tests.xml", "local");
} catch (Exception $e) {
    echo $e->getMessage();
}
