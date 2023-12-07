<?php

include('connect.php');
require __DIR__ . '../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;

$headers = getallheaders();
if (!isset($headers['Authorization']) || empty($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["error" => "unauthorized"]);
    exit();
}

$authorizationHeader = $headers['Authorization'];
$token = null;

$token = trim(str_replace("Bearer", '', $authorizationHeader));

if (!$token) {
    http_response_code(401);
    echo json_encode(["error" => "unauthorized"]);
    exit();
}
try {
    $key = "nabiha";
    $decoded = JWT::decode($token, new Key($key, 'HS256'));
    if ($decoded->usertype == 0) {
        //seller
        $query = $mysqli->prepare('select * from product');
        $query->execute();
        $array = $query->get_result();
        $response = [];
        $response["permissions"] = true;
        while ($restaurant = $array->fetch_assoc()) {
            $response[] = $restaurant;
        }
    } else {

        $response = [];
        $response["permissions"] = false;
    }
    echo json_encode($response);
} catch (ExpiredException $e) {
    http_response_code(401);
    echo json_encode(["error" => "expired"]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token"]);
}
