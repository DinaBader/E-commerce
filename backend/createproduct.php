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
    $product_name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    if ($decoded->usertype == 0) {

        //seller
        $query = $mysqli->prepare('INSERT INTO product (name, description, price) VALUES (?, ?, ?)');
        $query->bind_param('ssi', $product_name, $description, $price);
        $query->execute();
        $affectedRows = $query->affected_rows;
        $productId = $mysqli->insert_id;

        $queryDetail = $mysqli->prepare('INSERT INTO product_detail (product_id, user_id) VALUES (?, ?)');
        $queryDetail->bind_param('ii', $productId, $decoded->user_id);
        $queryDetail->execute();
        $affectedRowsDetail = $queryDetail->affected_rows;

        $response = [];
        $response["permissions"] = true;

        if ($affectedRows > 0 && $affectedRowsDetail > 0) {
            $response["inserted"] = true;
        } else {
            $response["inserted"] = false;
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
    echo json_encode(["error" => "Invalid token: " . $e->getMessage()]);

}
