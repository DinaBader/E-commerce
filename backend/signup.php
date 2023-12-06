<?php
include('connect.php');

$email = $_POST['email'];
$fname = $_POST['fname'];
$lname = $_POST['lname'];
$password = $_POST['password'];
$gender = $_POST['gender'];
$usertype = $_POST['usertypes_id'];

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$query = $mysqli->prepare('INSERT INTO users (email, fname, lname, password, gender, usertypes_id) 
VALUES (?, ?, ?, ?, ?, ?)');
$query->bind_param('sssssi', $email, $fname, $lname, $hashed_password, $gender, $usertype);
$query->execute();

$response = [];
$response["status"] = "true";

echo json_encode($response);
?>
