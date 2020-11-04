<?php
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
session_start();

// csrf check
$token=$_POST['token'];
if ($token != $_SESSION['token'] || !isset($_SESSION['id'])) {
    echo json_encode(array(
        "success" => false,
        "message" => "Unauthorized request"
    ));
    exit();
}

$event_id=(int)$_POST["id"];
$recipients = preg_match('/[A-Za-z0-9_\s]*$/', $_POST['recipients']) ? $_POST['recipients'] : "";

if ($recipients == "") {
    echo json_encode(array(
        "success" => false,
        "message" => "You need to specify recipients to share this event with."
    ));
    exit();
}
$mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
// fetch original event
$stmt_fetchOriginalEvent = $mysqli->prepare("select year,month,date,hour,minute,title,description,user_id,author_id,group_id,tag_id from events where id=?");
$stmt_fetchOriginalEvent->bind_param('i', $event_id);
$stmt_fetchOriginalEvent->execute();
$stmt_fetchOriginalEvent->bind_result($year, $month, $date, $hour, $minute, $title, $description, $user_id, $author_id, $group_id, $tag_id);
$stmt_fetchOriginalEvent->fetch();
$stmt_fetchOriginalEvent->close();
// make sure it's not a group event
if($group_id){
    echo json_encode(array(
        "success" => false,
        "message" => "Group event is not allowed to share to other users."
    ));
    exit();
}
// only author can share the event
if($author_id != $_SESSION["id"]){
    echo json_encode(array(
        "success" => false,
        "message" => "Only the author has the right to share this event."
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
        exit();
    } else {
        array_push($ids, $user_id);
    }
    $stmt_findUser->close();
}
// user can't share to himself
if(in_array($_SESSION["id"], $ids)){
    echo json_encode(array(
        "success" => false,
        "message" => "You can't share to yourself."
    ));
    exit();
}

// insert shared event
foreach ($ids as $user_id) {
    // make sure the event is only shared once
    // check if shared event already exists for this recipient
    $stmt_fetchSharedEvent = $mysqli->prepare("select id from events where original_id=? AND user_id=?");
    $stmt_fetchSharedEvent->bind_param('ii', $event_id, $user_id);
    $stmt_fetchSharedEvent->execute();
    $stmt_fetchSharedEvent->bind_result($existing_event_id);
    $stmt_fetchSharedEvent->fetch();
    $stmt_fetchSharedEvent->close();
    if($existing_event_id){
        echo json_encode(array(
            "success" => false,
            "message" => "This event is already shared to " . $username . ". Please try again.",
            "existing event"=>$existing_event_id
        ));
        exit();
    }

    //add shared event to all shared users
    $stmt_insertShared = $mysqli->prepare("insert into events (original_id, year, month,date,hour,minute,title,description,user_id,author_id,tag_id) values (?,?,?,?,?,?,?,?,?,?,?)");
    $stmt_insertShared->bind_param('iiiiiissiii', $event_id, $year, $month, $date,$hour,$minute,$title,$description,$user_id,$author_id,$tag_id);
    $stmt_insertShared->execute();
    $stmt_insertShared->close();
}

echo json_encode(array(
    "success" => true
));

// success or not: user doesn't exist
?>