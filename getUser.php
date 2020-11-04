<?php
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
session_start();
if (isset($_SESSION['id'])) {
    echo json_encode(array("active" => true, "id" => $_SESSION['id'], "username" => htmlentities($_SESSION['username'])));
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
//} or
//{
//    active:false
//}
?>