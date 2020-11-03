<?php
//header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
session_start();

$event_id=(int)$_POST["id"];
$recipients = preg_match('/[A-Za-z0-9_\s]*$/', $_POST['recipients']) ? $_POST['recipients'] : "";

if ($recipients == "") {
    echo json_encode(array(
        "success" => false,
        "message" => "You need to specify recipients to share this event with"
    ));
} else {
    $mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
    // fetch original event
    $stmt_fetchEvent = $mysqli->prepare("select year,month,date,hour,minute,title,description,user_id,author_id,tag_id from events where id=?");
    $stmt_fetchEvent->bind_param('i', $event_id);
    $stmt_fetchEvent->execute();
    $stmt_fetchEvent->bind_result($year, $month, $date, $hour, $minute, $title, $description, $user_id, $author_id, $tag_id);
    $stmt_fetchEvent->fetch();
    $stmt_fetchEvent->close();
    if($author_id != $_SESSION["id"]){
        echo json_encode(array(
            "success" => false,
            "message" => "You are not the author, you don't have the right to share this event"
        ));
        exit();
    }

    $recipients = explode(" ", $recipients);
    $ids = array();

    //fetch all recipients
    // lookup user id
    foreach ($recipients as $username) {
        $stmt_findUser = $mysqli->prepare("select id from users where username=?");
        $stmt_findUser->bind_param('s', $username);
        $stmt_findUser->execute();
        $stmt_findUser->bind_result($user_id);
        $stmt_findUser->fetch();
        // if no user was found
        if ($user_id == null) {
            // cannot find user
            echo json_encode(array(
                "success" => false,
                "message" => "Cannot find user " . $username
            ));
            exit;
        } else {
            array_push($ids, $user_id);
        }
        $stmt_findUser->close();
    }

    // insert shared event
    foreach ($ids as $user_id) {
        //add shared event to all shared users
        $stmt_insertShared = $mysqli->prepare("insert into events (original_id, year, month,date,hour,minute,title,description,user_id,author_id,tag_id) values (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt_insertShared->bind_param('iiiiiissiii', $event_id, $year, $month, $date,$hour,$minute,$title,$description,$user_id,$author_id,$tag_id);
        $stmt_insertShared->execute();
        $stmt_insertShared->close();
    }

    echo json_encode(array(
        "success" => true,
        "recipients"=>$ids
    ));

}



// success or not: user doesn't exist
?>