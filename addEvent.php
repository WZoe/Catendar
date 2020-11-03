<?php
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
session_start();

$title = preg_match('/[A-Za-z0-9_\s*#<>?!.,"\']+$/', $_POST['title']) ? $_POST['title'] : "";
$year = (int)$_POST['year'];
$month = (int)$_POST['month'];
$date = (int)$_POST['date'];
$hour = (int)$_POST['hour'];
$minute = (int)$_POST['minute'];
$description = preg_match('/[^<>:]+$/', $_POST['description']) ? $_POST['description'] : "";
$tag = (int)$_POST['tag'];

if ($title == "") {
    echo json_encode(array(
       "success" => false,
       "message" => "Event title can't be empty"
    ));
} else {
    $mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
    $stmt = $mysqli->prepare("insert into events (original_id,year, month,date,hour,minute,title,description,user_id,author_id,tag_id) values (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('iiiiiissiii', $_SESSION['id'],$year, $month, $date,$hour,$minute,$title,$description,$_SESSION["id"],$_SESSION["id"],$tag);
    $stmt->execute();
    $stmt->close();

    echo json_encode(array(
        "success" => true,
    ));
}

// todo:add group add events edit events share events CSRF
//todo:make sure user is logged in