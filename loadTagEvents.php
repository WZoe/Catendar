<?php
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
session_start();

// retrieve data posted via fetch()
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
$tag_id = (int)$json_obj['tag_id'];
$currentYear = (int)$json_obj['currentYear'];
$currentMonth = (int)$json_obj['currentMonth'];
$prevMonth = (int)$json_obj['prevMonth'];
$nextMonth = (int)$json_obj['nextMonth'];
if ($prevMonth != -1) {
    $prevMonthYear = (int)$json_obj['prevMonthYear'];
    $prevMonthStartDate = (int)$json_obj['prevMonthStartDate'];
}
if ($nextMonth != -1) {
    $nextMonthYear = (int)$json_obj['nextMonthYear'];
    $nextMonthEndDate = (int)$json_obj['nextMonthEndDate'];
}

// csrf check
$token = $json_obj['token'];
if ($token != $_SESSION['token']) {
    echo json_encode(array(
        "success" => false,
        "message" => "Unauthorized request"
    ));
    exit();
}
// make sure user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(array(
        "success" => false,
        "message" => "user is not logged in"
    ));
} else {
    $personal_events = array();
    $shared_events = array();
    $group_events = array();

    // fetch daily events by time
    // fetch personal events
    $mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
    $stmt_personal = $mysqli->prepare("SELECT id, year, month, date, hour, minute, title, description, author_id FROM events WHERE tag_id=? AND user_id=? AND author_id=? AND ((year=? AND month=?) OR (month=? AND year=? AND date>=?) OR (month=? AND year=? AND date<=?)) ORDER BY hour, minute");
    $stmt_personal->bind_param('iiiiiiiiiii', $tag_id, $_SESSION["id"], $_SESSION["id"], $currentYear, $currentMonth, $prevMonth, $prevMonthYear, $prevMonthStartDate, $nextMonth, $nextMonthYear, $nextMonthEndDate);
    $stmt_personal->execute();
    $stmt_personal->bind_result($event_id, $year, $month, $date, $hour, $minute, $title, $description, $author_id);
    while ($stmt_personal->fetch()) {
        // get author username
        $mysqli2 = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
        $stmt_author = $mysqli2->prepare("SELECT username FROM users WHERE id=?");
        $stmt_author->bind_param('i', $author_id);
        $stmt_author->execute();
        $stmt_author->bind_result($author_name);
        $stmt_author->fetch();
        $stmt_author->close();
        $dateNo = $month + "-" + $date;
        if (!array_key_exists($dateNo, $personal_events)) {
            $personal_events[$dateNo] = array();
        }
        array_push($personal_events[$dateNo], array(
            "event_id" => $event_id,
            "year" => $year,
            "month" => $month,
            "date" => $date,
            "hour" => $hour,
            "minute" => $minute,
            "title" => htmlentities($title),
            "description" => nl2br(htmlentities($description)),
            "author_name" => htmlentities($author_name)
        ));
    }
    $stmt_personal->close();

    // fetch shared events
    $stmt_shared = $mysqli->prepare("SELECT id, year, month, date, hour, minute, title, description, author_id FROM events WHERE tag_id=? AND user_id=? AND author_id!=? AND ((year=? AND month=?) OR (month=? AND year=? AND date>=?) OR (month=? AND year=? AND date<=?)) ORDER BY hour, minute");
    $stmt_shared->bind_param('iiiiiiiiiii', $tag_id, $_SESSION["id"], $_SESSION["id"], $currentYear, $currentMonth, $prevMonth, $prevMonthYear, $prevMonthStartDate, $nextMonth, $nextMonthYear, $nextMonthEndDate);
    $stmt_shared->execute();
    $stmt_shared->bind_result($event_id, $year, $month, $date, $hour, $minute, $title, $description, $author_id);
    while ($stmt_shared->fetch()) {
        // get author username
        $mysqli2 = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
        $stmt_author = $mysqli2->prepare("SELECT username FROM users WHERE id=?");
        $stmt_author->bind_param('i', $author_id);
        $stmt_author->execute();
        $stmt_author->bind_result($author_name);
        $stmt_author->fetch();
        $stmt_author->close();
        $dateNo = $month + "-" + $date;
        if (!array_key_exists($dateNo, $shared_events)) {
            $shared_events[$dateNo] = array();
        }
        array_push($shared_events[$dateNo], array(
            "event_id" => $event_id,
            "year" => $year,
            "month" => $month,
            "date" => $date,
            "hour" => $hour,
            "minute" => $minute,
            "title" => htmlentities($title),
            "description" => nl2br(htmlentities($description)),
            "author_name" => htmlentities($author_name)
        ));
    }
    $stmt_shared->close();

    // fetch group events
    // get groups user belongs to
    $groups = array();
    $stmt_group1 = $mysqli->prepare("SELECT group_id from groups_users where user_id=?");
    $stmt_group1->bind_param('i', $_SESSION["id"]);
    $stmt_group1->execute();
    $stmt_group1->bind_result($group_id);
    while ($stmt_group1->fetch()) {
        array_push($groups, $group_id);
    }
    $stmt_group1->close();

    foreach ($groups as $group_id) {
        $stmt_group2 = $mysqli->prepare("SELECT id, year, month, date, hour, minute, title, description, author_id FROM events WHERE tag_id=? AND group_id=? AND ((year=? AND month=?) OR (month=? AND year=? AND date>=?) OR (month=? AND year=? AND date<=?)) ORDER BY hour, minute");
        $stmt_group2->bind_param('iiiiiiiiii', $tag_id, $group_id, $currentYear, $currentMonth, $prevMonth, $prevMonthYear, $prevMonthStartDate, $nextMonth, $nextMonthYear, $nextMonthEndDate);
        $stmt_group2->execute();
        $stmt_group2->bind_result($event_id, $year, $month, $date, $hour, $minute, $title, $description, $author_id);
        while ($stmt_group2->fetch()) {
            $mysqli2 = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
            // get author username
            $stmt_author = $mysqli2->prepare("SELECT username FROM users WHERE id=?");
            $stmt_author->bind_param('i', $author_id);
            $stmt_author->execute();
            $stmt_author->bind_result($author_name);
            $stmt_author->fetch();
            $stmt_author->close();
            // get group name
            $stmt_group3 = $mysqli2->prepare("SELECT name FROM groups WHERE id=?");
            $stmt_group3->bind_param('i', $group_id);
            $stmt_group3->execute();
            $stmt_group3->bind_result($group_name);
            $stmt_group3->fetch();
            $stmt_group3->close();
            $dateNo = $month + "-" + $date;
            if (!array_key_exists($dateNo, $group_events)) {
                $group_events[$dateNo] = array();
            }
            array_push($group_events[$dateNo], array(
                "event_id" => $event_id,
                "year" => $year,
                "month" => $month,
                "date" => $date,
                "hour" => $hour,
                "minute" => $minute,
                "title" => htmlentities($title),
                "description" => nl2br(htmlentities($description)),
                "author_name" => htmlentities($author_name),
                "group_name" => htmlentities($group_name)
            ));
        }
        $stmt_group2->close();
    }

    // return daily events as json array
    echo json_encode(array(
        "success" => true,
        "personal_events" => $personal_events,
        "shared_events" => $shared_events,
        "group_events" => $group_events
    ));
}
?>