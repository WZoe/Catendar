<?php
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
session_start();

$mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
$stmt = $mysqli->prepare("select group_id from groups_users where user_id=?");
if ($stmt) {
    $stmt->bind_param('i', $_SESSION['id']);
    $stmt->execute();
    $stmt->bind_result($group_id);
    $result = array();
    while ($stmt->fetch()) {
        // look up group names
        $mysqli2 = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
        $stmt2 = $mysqli2->prepare("select name from groups where id=?");
        $stmt2->bind_param('i', $group_id);
        $stmt2->execute();
        $stmt2->bind_result($name);
        $stmt2->fetch();
        $stmt2->close();

        // append to result array
        array_push($result, array(
            "group_id" => $group_id,
            "group_name" => htmlentities($name)
        ));
    }
    $stmt->close();
}
echo json_encode($result);

?>