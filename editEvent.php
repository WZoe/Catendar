<?php
header("Content-Type: application/json");
ini_set("session.cookie_httponly", 1);
session_start();
if (!isset($_SESSION['id'])) {
    echo json_encode(array(
        "success"=>false,
        "message"=>"user is not logged in"
    ));
}
else{
    $event_id=(int)$_POST["id"];

//    $event_id=46;
    //fetch event detail
    $mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
    $stmt = $mysqli->prepare("select user_id, author_id, group_id from events where id=?");
    $stmt->bind_param('i', $event_id);
    $stmt->execute();
    $stmt->bind_result($user_id, $author_id, $group_id);
    $stmt->fetch();
    $stmt->close();

    $event_group_id = -1;
    $event_user_id = -1;
    // make sure user has access to this event
    // only author can edit
    if($author_id != $_SESSION["id"]){
        echo json_encode(array(
            "success" => false,
            "message" => "You have no access to edit this event"
        ));
        exit();
    }
    else if ($user_id != $_SESSION["id"]) {
        //check groups
        //get group ids
        $stmt = $mysqli->prepare("select group_id from groups_users where user_id=?");
        $stmt->bind_param('i', $_SESSION['id']);
        $stmt->execute();
        $stmt->bind_result($user_group_id);
        $user_group_ids = array();
        while ($stmt->fetch()) {
            array_push($user_group_ids,$user_group_id);
        }
        if (!in_array($group_id, $user_group_ids)) {
            // no access
            echo json_encode(array(
                "success" => false,
                "message" => "You have no access to edit this event"
            ));
            exit();
        }
        else{  // this is a group event
            $event_group_id = $group_id;
        }
    }
    else{  // this is a user event
        $event_user_id = $user_id;
    }

    $title = preg_match('/[A-Za-z0-9_\s*#<>?!.,"\']+$/', $_POST['title']) ? $_POST['title'] : "";
    $year = (int)$_POST['year'];
    $month = (int)$_POST['month'];
    $date = (int)$_POST['date'];
    $hour = (int)$_POST['hour'];
    $minute = (int)$_POST['minute'];
    $description = preg_match('/[^<>:]+$/', $_POST['description']) ? $_POST['description'] : "";
    $tag = (int)$_POST['tag'];

    if ($title == "") {
        echo json_encode(array(
        "success" => false,
        "message" => "Event title can't be empty"
        ));
    } else {
        // get user_id / group_id
        $mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
        $stmt_getId = 
        
        $stmt_edit = $mysqli->prepare("UPDATE events SET year=?, month=?, date=?, hour=?, minute=?, title=?, description=?, tag_id=? WHERE id=? AND user_id=?");
        $stmt_edit->bind_param('iiiiissi', $year, $month, $date,$hour,$minute,$title,$description,$tag, $event_id, $_SESSION["id"]);
        $stmt_edit->execute();
        $stmt_edit->close();

        // get user event
        $stmt_loadUpdatedUser = $mysqli->prepare("SELECT id, year, month, date, hour, minute, title, description, tag_id, author_id FROM events WHERE user_id=?");
        $stmt_loadUpdatedUser->bind_param('i', $user_id);
        $stmt_loadUpdatedUser->execute();
        $stmt_loadUpdatedUser->bind_result($event_id, $year, $month, $date, $hour, $minute, $title, $description, $tag_id, $author_id);
        $stmt_loadUpdatedUser->fetch();
        $stmt_loadUpdatedUser->close();
        // get group event
        $stmt_loadUpdatedGroup = $mysqli->prepare("SELECT id, year, month, date, hour, minute, title, description, tag_id, author_id FROM events WHERE group_id=?");
        $stmt_loadUpdatedGroup->bind_param('i', $group_id);
        $stmt_loadUpdatedGroup->execute();
        $stmt_loadUpdatedGroup->bind_result($event_id, $year, $month, $date, $hour, $minute, $title, $description, $tag_id, $author_id);
        $stmt_loadUpdatedGroup->fetch();
        $stmt_loadUpdatedGroup->close();

        echo json_encode(array(
            "success" => true,
            "id"=>$event_id,
            "year"=>$year,
            "month"=>$month,
            "date"=>$date,
            "hour"=>$hour,
            "minute"=>$minute,
            "title"=>$title,
            "description"=>$description,
            "tag"=>$tag_id,
            "author"=>$author_id
        ));
    }

    // todo:add group add events edit events share events CSRF
    //todo:make sure user is logged in
}

