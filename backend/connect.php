<?php
header('Access-Controll-Allow-Origin:*');
$host="localhost";
$db_user="root";
$db_pass=null;
$db_name="ecommerce";

$mysqli=new mysqli($host,$db_user,$db_pass,$db_name);
if($mysqli->connect_error){
    die("".$muysqli->connect_error);
}else{
    
}