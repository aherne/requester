<?php
session_start();
header("Content-Type: application/json");
if (!empty($_GET["newcookie"])) {
    setcookie("hello", "world", time()+3600, "/");
}
file_put_contents("test.json", json_encode($_SERVER));
if ($_SERVER["REQUEST_METHOD"]=="PUT") {
    $input = file_get_contents("php://input");
    file_put_contents(__DIR__."/upload.json", $input);
} elseif ($_SERVER["REQUEST_METHOD"]=="POST" && $input = file_get_contents("php://input")) {
    file_put_contents(__DIR__."/upload.json", $input);
} else {
    echo json_encode(["headers"=>getallheaders(), "request"=>$_REQUEST, "server"=>$_SERVER, "files"=>$_FILES, "session"=>$_SESSION, "body"=>"OK"]);
}
