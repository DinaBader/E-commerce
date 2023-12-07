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
        $product_id = $_POST['product_id'];
        $query = $mysqli->prepare('delete from product where product_id=?');
        $query->bind_param('i',$product_id);
        $query->execute();
        $rowsAffected = $query->affected_rows;

        $queryDetail=$mysqli->prepare('delete from product_detail where product_id=? and user_id=?');
        $queryDetail->bind_param('ii',$product_id,$decoded->user_id);
        $queryDetail->execute();
        $rowsAffecttedDetail=$query->affected_rows;
        if ($rowsAffected > 0 && $rowsAffecttedDetail>0) {
            echo json_encode(["status" => "success", "message" => "Product deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Product not found or you don't have permission to delete"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    }
} catch (ExpiredException $e) {
    http_response_code(401);
    echo json_encode(["error" => "expired"]);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token: " . $e->getMessage()]);
}