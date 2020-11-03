<?php
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
session_start();

if (!isset($_SESSION['id'])) {
    echo json_encode(array(
        "success"=>false,
        "message"=>"user is not logged in"
    ));
} else {
    $event_id=(int)$_POST["id"];

    //fetch event detail
    $mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
    $stmt = $mysqli->prepare("select year, month,date,hour,minute,title,description,user_id,group_id, author_id,tag_id from events where id=?");
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $stmt->bind_result($year, $month, $date, $hour, $minute, $title, $description, $user_id, $group_id, $author_id, $tag_id);
    $stmt->fetch();
    $stmt->close();

    // make sure user has access to this event
    //check user id
    if ($user_id != $_SESSION["id"]) {
        //check groups
        //get group ids
        $stmt = $mysqli->prepare("select group_id from groups_users where user_id=?");
        $stmt->bind_param('i', $_SESSION['id']);
        $stmt->execute();
        $stmt->bind_result($user_group_id);
        $user_group_ids = array();
        while ($stmt->fetch()) {
            array_push($user_group_ids,$user_group_id);
        }
        if (in_array($group_id, $user_group_ids)) {
            // no access
            echo json_encode(array(
                "success" => false,
                "message" => "You have no access to this event"
            ));
            exit();
        }
    }

    //get user name
    // get group name
    echo json_encode(array(
        "success" => true,
        "year" => $year,
        "month" =>$month,
        "date"=>$date,
        "hour"=>$hour,
        "minute"=>$minute,
        "title"=>$title,
        "description"=>$description,
        "user_id"=>$user_id,
        "group_id"=>$group_id,
        "author_id"=>$author_id,
        "tag_id"=>$tag_id
    ));

}