<?php
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
session_start();

// retrieve data posted via fetch()
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);
$group_id = (int)$json_obj['group_id'];
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
    // fetch group events
    $mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
    $stmt_group1 = $mysqli->prepare("SELECT id, year, month, date, hour, minute, title, description, tag_id, author_id FROM events WHERE group_id=? AND ((year=? AND month=?) OR (month=? AND year=? AND date>=?) OR (month=? AND year=? AND date<=?)) ORDER BY hour, minute");
    $stmt_group1->bind_param('iiiiiiiii', $group_id, $currentYear, $currentMonth, $prevMonth, $prevMonthYear, $prevMonthStartDate, $nextMonth, $nextMonthYear, $nextMonthEndDate);
    $stmt_group1->execute();
    $stmt_group1->bind_result($event_id, $year, $month, $date, $hour, $minute, $title, $description, $tag_id, $author_id);
    while ($stmt_group1->fetch()) {
        $mysqli2 = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
        // get author username
        $stmt_author = $mysqli2->prepare("SELECT username FROM users WHERE id=?");
        $stmt_author->bind_param('i', $author_id);
        $stmt_author->execute();
        $stmt_author->bind_result($author_name);
        $stmt_author->fetch();
        $stmt_author->close();
        // get group name
        $stmt_group2 = $mysqli2->prepare("SELECT name FROM groups WHERE id=?");
        $stmt_group2->bind_param('i', $group_id);
        $stmt_group2->execute();
        $stmt_group2->bind_result($group_name);
        $stmt_group2->fetch();
        $stmt_group2->close();
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
            "tag_id" => $tag_id,
            "author_name" => htmlentities($author_name),
            "group_name" => htmlentities($group_name)
        ));
    }
    $stmt_group1->close();

    // return daily events as json array
    echo json_encode(array(
        "success" => true,
        "personal_events" => $personal_events,
        "shared_events" => $shared_events,
        "group_events" => $group_events
    ));
}
?>