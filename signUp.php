<?php
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);

$username = preg_match('/[A-Za-z0-9_]+$/', $_POST['username']) ? $_POST['username'] : "";
$password = preg_match('/[A-Za-z0-9_]+$/', $_POST['password']) ? $_POST['password'] : "";
if ($password == "" || $username == "") {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid username or password"
    ));
} else {
    $password = password_hash($password, PASSWORD_DEFAULT);

    // The following are modifed from our group project 3
    $mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
    // look up database to see if there is existing user
    $stmt = $mysqli->prepare("select id from users where username=?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        // user exist, alert
        echo json_encode(array(
            "success" => false,
            "message" => "Username already exists"
        ));
    } else {
        //create new entry.
        $stmt1 = $mysqli->prepare("insert into users (username, password) values (?,?)");
        if ($stmt1) {
            $stmt1->bind_param('ss', $username, $password);
            $stmt1->execute();
            $id = $mysqli->insert_id;
            $stmt1->close();

            //set session
            if (!isset($_SESSION)) {
                session_start();
            } else {
                unset($_SESSION['id']);
                unset($_SESSION['username']);
                unset($_SESSION['token']);
                session_destroy();
                session_start();
            }
            $_SESSION['id'] = $id;
            $_SESSION['username'] = htmlentities($username);
            $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
        $stmt->close();
        echo json_encode(array(
           "success" => true,
            "username" => $_SESSION['username'],
            "token" => $_SESSION["token"]
        ));
    }

}

?>