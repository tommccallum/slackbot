<?php

# Check server is receiving what we think it should be

print("CONTENT TYPE:\n");
print($_SERVER['CONTENT_TYPE']."\n");

$json = file_get_contents('php://input');

// Converts it into a PHP object
$data = json_decode($json);
file_put_contents("hello.log", date("Y-m-d H:i:s T")."\n");
file_put_contents("hello.log", $json."\n", FILE_APPEND);
file_put_contents("hello.log", json_encode($_REQUEST)."\n", FILE_APPEND);
file_put_contents("hello.log", json_encode($_GET)."\n", FILE_APPEND);
file_put_contents("hello.log", json_encode($_POST)."\n", FILE_APPEND);

print("JSON INPUT:\n");
print($json);
print("\n");
print("REQUEST ARRAY:\n");
print(json_encode($_REQUEST));
print("\n");
print("GET ARRAY:\n");
print(json_encode($_GET));
print("\n");
print("POST ARRAY:\n");
