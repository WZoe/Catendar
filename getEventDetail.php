<?php
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
session_start();

$token = $_POST['token'];

// check token & status
if ($token != $_SESSION['token']) {
    echo json_encode(array(
        "success" => false,
        "message" => "Unauthorized request"
    ));
    exit();
}

if (!isset($_SESSION['id'])) {
    echo json_encode(array(
        "success" => false,
        "message" => "user is not logged in"
    ));
} else {
    $event_id = (int)$_POST["id"];

//    $event_id=46;
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
            array_push($user_group_ids, $user_group_id);
        }
        if (!in_array($group_id, $user_group_ids)) {
            // no access
            echo json_encode(array(
                "success" => false,
                "message" => "You have no access to this event"
            ));
            exit();
        }
    }

    //get author name
    $stmt = $mysqli->prepare("select username from users where id=?");
    $stmt->bind_param('i', $author_id);
    $stmt->execute();
    $stmt->bind_result($author);
    $stmt->fetch();
    $stmt->close();
    // get group name
    if ($group_id) {
        $stmt = $mysqli->prepare("select name from groups where id=?");
        $stmt->bind_param('i', $group_id);
        $stmt->execute();
        $stmt->bind_result($group);
        $stmt->fetch();
        $stmt->close();
    } else {
        $group = null;
    }
    //get tag name
    $stmt = $mysqli->prepare("select name from tags where id=?");
    $stmt->bind_param('i', $tag_id);
    $stmt->execute();
    $stmt->bind_result($tag);
    $stmt->fetch();
    $stmt->close();

    //shared?
    $shared = $user_id == $author_id ? false : true;

    echo json_encode(array(
        "success" => true,
        "year" => $year,
        "month" => $month,
        "date" => $date,
        "hour" => $hour,
        "minute" => $minute,
        "title" => htmlentities($title),
        "description" => nl2br(htmlentities($description)),
        "user_id" => $user_id,
        "group" => htmlentities($group),
        "author" => htmlentities($author),
        "tag" => htmlentities($tag),
        "shared" => $shared
    ));

}
?>