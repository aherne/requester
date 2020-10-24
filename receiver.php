<?php
echo json_encode(["headers"=>getallheaders(), "parameters"=>$_REQUEST, "body"=>"Hello!"]);
