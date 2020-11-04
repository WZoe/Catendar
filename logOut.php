<?php
ini_set("session.cookie_httponly", 1);
// this is modified from our code in module 3 group
session_start();
unset($_SESSION['id']);
unset($_SESSION['username']);
unset($_SESSION['token']);
session_destroy();

?>