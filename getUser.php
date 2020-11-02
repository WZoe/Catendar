<?php
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
session_start();
if (isset($_SESSION['id'])) {
    echo json_encode(array("active" => true, "id" =>  $_SESSION['id'], "username" => $_SESSION['username'], "token"=>$_SESSION['token']));
    exit;
} else {
    echo json_encode(array("active" => false));
    session_destroy();
    exit;
}

//echo json_encode(array("active"=>true, "id" =>1, "username"=>"zoe"));

// this php file returns the current session user with
//{
//    active: true,
//    id:,
//    username:
//      token:
//} or
//{
//    active:false
//}