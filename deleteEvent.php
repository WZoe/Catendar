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
    $stmt = $mysqli->prepare("select user_id,group_id, author_id from events where id=?");
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $stmt->bind_result($user_id, $group_id, $author_id);
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
        if (!in_array($group_id, $user_group_ids)) {
            // no access
            echo json_encode(array(
                "success" => false,
                "message" => "You have not allowed to delete this event."
            ));
            exit();
        }
    }


    //delete this event
    $stmt = $mysqli->prepare("delete from events where id=? or original_id=?");
    $stmt->bind_param('i', $_SESSION['id']);
    $stmt->execute();
    $stmt->close();

    // delete all copies
}