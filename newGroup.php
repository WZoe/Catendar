<?php
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
session_start();

$name = preg_match('/[A-Za-z0-9_\s*#<>?!.,"\']+$/', $_POST['name']) ? $_POST['name'] : "";
$members = preg_match('/[A-Za-z0-9_\s]*$/', $_POST['members']) ? $_POST['members'] : "";
$token = $_POST['token'];

// check token & status
if ($token != $_SESSION['token'] || !isset($_SESSION['id'])) {
    echo json_encode(array(
        "success" => false,
        "message" => "Unauthorized request"
    ));
    exit();
}
if ($name == "" || $members == "") {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid group name or no member is assigned"
    ));
} else {
    $members = explode(" ", $members);
    $mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
    $ids = array();
    array_push($ids, $_SESSION["id"]);

    //fetch all members
    // lookup user id
    foreach ($members as $username) {
        $stmt = $mysqli->prepare("select id from users where username=?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->bind_result($user_id);
        $stmt->fetch();
        // if no user was found
        if ($user_id == null) {
            // cannot find user
            echo json_encode(array(
                "success" => false,
                "message" => "Cannot find user " . htmlentities($username)
            ));
            exit;
        } else {
            array_push($ids, $user_id);
        }
        $stmt->close();
    }


//create new group
    $stmt1 = $mysqli->prepare("insert into groups (name) values (?)");
    $stmt1->bind_param('s', $name);
    $stmt1->execute();
    $group_id = $mysqli->insert_id;
    $stmt1->close();

    // add members
    foreach ($ids as $user_id) {
        // add to relationship
        $stmt = $mysqli->prepare("insert into groups_users (group_id, user_id) values (?,?)");
        $stmt->bind_param('ii', $group_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    echo json_encode(array(
        "success" => true,
    ));

}


// success or not: user doesn't exist
?>