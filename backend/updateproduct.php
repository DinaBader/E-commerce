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
        if (isset($_POST["product_id"], $_POST["price"], $_POST["name"], $_POST["description"])) {
            $product_id = $_POST["product_id"];
            $product_price = $_POST["price"];
            $product_name = $_POST['name'];
            $product_description = $_POST['description'];

            $query = $mysqli->prepare('UPDATE product SET name=?, description=?, price=? WHERE product_id=? AND user_id=?');
            $query->bind_param('ssiii', $product_name, $product_description, $product_price, $product_id, $decoded->user_id);
            $query->execute();
            $rowsAffected = $query->affected_rows;

            if ($rowsAffected > 0) {
                echo json_encode(["status" => "success", "message" => "Product updated successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Product not found or you don't have permission to update"]);
            }
        } else {
            
            $missingFields = [];
            if (!isset($_POST["product_id"])) {
                $missingFields[] = "product_id";
            }
            if (!isset($_POST["price"])) {
                $missingFields[] = "price";
            }
            if (!isset($_POST["name"])) {
                $missingFields[] = "name";
            }
            if (!isset($_POST["description"])) {
                $missingFields[] = "description";
            }
        
            http_response_code(400); // Bad Request
            echo json_encode(["error" => "Missing required field(s): " . implode(', ', $missingFields)]);
            exit();
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
?>
