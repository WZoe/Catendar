<?php
    if (!isset($_SESSION)) {
        session_start();
        $_SESSION['user_id'] = 1;
    }

    // retrieve json data from ajax
    header("Content-Type: application/json");
    // retrieve data posted via fetch()
    $json_str = file_get_contents('php://input');
    $json_obj = json_decode($json_str, true);
    $year = $json_obj['year'];
    $month = $json_obj['month'];
    $date = $json_obj['date'];

    // TODO: verify user, sesssion start
    // if (!$_SESSION) {
    //     session_start();
    // }
    // // make sure user is logged in
    // if (!isset($_SESSION['user_id'])) {
    //     header("Location: index.html");
    //     // TODO: 看是否改成json_encode success=false的形式
    // } else {
        $personal_events = array();
        $shared_events = array();
        $group_events = array();

        // fetch daily events by time
        // fetch personal events
        $mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
        $stmt_personal = $mysqli->prepare("SELECT id, hour, minute, title, description, tag_id, author_id FROM events WHERE user_id=? AND author_id=? AND year=? AND month=? AND date=? ORDER BY hour, minute");
        $stmt_personal->bind_param('iiiii', $_SESSION["user_id"], $_SESSION["user_id"], $year, $month, $date);
        $stmt_personal->execute();
        $stmt_personal->bind_result($event_id, $hour, $minute, $title, $description, $tag_id, $author_id);
        while ($stmt_personal->fetch()) {
            // get author username
            // $stmt_author = $mysqli->prepare("SELECT username FROM users WHERE id=?");
            // $stmt_author->bind_param('i', $author_id);
            // $stmt_author->execute();
            // $stmt_author->bind_result($author_name);
            // $stmt_author->fetch();
            // $stmt_author->close();
            array_push($personal_events, array(
                "event_id"=>$event_id,
                "hour"=>$hour,
                "minute"=>$minute,
                "title"=>$title,
                "description"=>$description,
                "tag_id"=>$tag_id,
                "author_name"=>$author_id
            ));
        }
        $stmt_personal->close();

        // fetch shared events
        $stmt_shared = $mysqli->prepare("SELECT id, hour, minute, title, description, tag_id, author_id FROM events WHERE user_id=? AND author_id!=? AND year=? AND month=? AND date=? ORDER BY hour, minute");
        $stmt_shared->bind_param('iiiii', $_SESSION["user_id"], $_SESSION["user_id"], $year, $month, $date);
        $stmt_shared->execute();
        $stmt_shared->bind_result($event_id, $hour, $minute, $title, $description, $tag_id, $author_id);
        while ($stmt_shared->fetch()) {
            // get author username
            // $stmt_author = $mysqli->prepare("SELECT username FROM users WHERE id=?");
            // $stmt_author->bind_param('i', $author_id);
            // $stmt_author->execute();
            // $stmt_author->bind_result($author_name);
            // $stmt_author->fetch();
            // $stmt_author->close();
            array_push($shared_events, array(
                "event_id"=>$event_id,
                "hour"=>$hour,
                "minute"=>$minute,
                "title"=>$title,
                "description"=>$description,
                "tag_id"=>$tag_id,
                "author_name"=>$author_id
            ));
        }
        $stmt_shared->close();

        // fetch group events
        // get groups user belongs to
        $groups = array();
        $stmt_group1 = $mysqli->prepare("SELECT group_id from groups_users where user_id=?");
        $stmt_group1->bind_param('i', $_SESSION["user_id"]);
        $stmt_group1->execute();
        $stmt_group1->bind_result($group_id);
        while($stmt_group1->fetch()){
            array_push($groups, $group_id);
        }
        $stmt_group1->close();

        foreach($groups as $group_id){
            $stmt_group2 = $mysqli->prepare("SELECT id, hour, minute, title, description, tag_id, author_id FROM events WHERE group_id=? AND year=? AND month=? AND date=? ORDER BY hour, minute");
            $stmt_group2->bind_param('iiii', $group_id, $year, $month, $date);
            $stmt_group2->execute();
            $stmt_group2->bind_result($event_id, $hour, $minute, $title, $description, $tag_id, $author_id);
            while ($stmt_group2->fetch()) {
                // get author username
                // $stmt_author = $mysqli->prepare("SELECT username FROM users WHERE id=?");
                // $stmt_author->bind_param('i', $author_id);
                // $stmt_author->execute();
                // $stmt_author->bind_result($author_name);
                // $stmt_author->fetch();
                // $stmt_author->close();
                // get group name
                // $stmt_group3 = $mysqli->prepare("SELECT name FROM groups WHERE id=?");
                // $stmt_group3->bind_param('i', $group_id);
                // $stmt_group3->execute();
                // $stmt_group3->bind_result($group_name);
                // $stmt_group3->fetch();
                // $stmt_group3->close();
                array_push($group_events, array(
                    "event_id"=>$event_id,
                    "hour"=>$hour,
                    "minute"=>$minute,
                    "title"=>$title,
                    "description"=>$description,
                    "tag_id"=>$tag_id,
                    "author_name"=>$author_id,
                    "group_name"=>$group_id
                ));
            }
            $stmt_group2->close();
        }

        // return daily events as json array
        echo json_encode(array(
            "success"=>true,
            "personal_events"=>$personal_events,
            "shared_events"=>$shared_events,
            "group_events"=>$group_events
        ));
    // }
?>