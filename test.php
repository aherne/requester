<?php
// to test, current dir must be writable (chmod 777 CURRENT-FOLDER)
require __DIR__ . '/vendor/autoload.php';
try {
    // copy file to localhost in order to test HTTP connection
    define("RECEIVER_HTTP", "http://localhost/requester/receiver.php");
    define("RECEIVER_FOLDER", "/var/www/html/requester");
    define("RECEIVER_HTTPS", "https://www.lucinda-framework.com/public/receiver.php");
    if (!file_exists(RECEIVER_FOLDER)) {
        throw new Exception("Please create writable RECEIVER_FOLDER to a place accessible by local webserver");
    }
    if (!file_exists(RECEIVER_FOLDER."/receiver.php")) {
        throw new Exception("Please copy receiver.php file to your RECEIVER_FOLDER");
    }
    if (!file_get_contents(RECEIVER_HTTP)) {
        throw new Exception("Please make RECEIVER_FOLDER accessible by local webserver");
    }
    new Lucinda\UnitTest\ConsoleController("unit-tests.xml", "local");
} catch (Exception $e) {
    echo $e->getMessage();
}
