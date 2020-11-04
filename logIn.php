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
    // the following is modified from our code in module 3-group
    // look up database
    $mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
    $stmt = $mysqli->prepare("select id, password from users where username=?");
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->bind_result($id, $true_password);
        $stmt->fetch();
        $stmt->close();
    }
    if (password_verify($password, $true_password)) {
        // validation passed, log in.
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
        $_SESSION['username'] = $username;
        if (empty($_SESSION['token'])) {
            $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
        echo json_encode(array(
            "success" => true,
            "username" => htmlentities($_SESSION['username']),
            "token" => $_SESSION['token']
        ));

    } else {
        // login failed
        echo json_encode(array(
            "success" => false,
            "message" => "Wrong username or password",
        ));
    }
}
?>