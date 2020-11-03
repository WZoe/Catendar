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

    //fetch event detail
    $mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
    $stmt_fetchOriginalEvent = $mysqli->prepare("select user_id, author_id, group_id from events where id=?");
    $stmt_fetchOriginalEvent->bind_param('i', $event_id);
    $stmt_fetchOriginalEvent->execute();
    $stmt_fetchOriginalEvent->bind_result($user_id, $author_id, $group_id);
    $stmt_fetchOriginalEvent->fetch();
    $stmt_fetchOriginalEvent->close();

    // make sure user has access to this event
    // personal or shared event
    if($user_id){
        // only author can edit
        if($author_id != $_SESSION["id"] || $user_id != $_SESSION["id"]){
            echo json_encode(array(
                "success" => false,
                "message" => "You are not allowed to edit this event"
            ));
            exit();
        }
    }
    // group event
    else if ($group_id) {
        //check groups
        //get group ids
        $stmt_getGroup = $mysqli->prepare("select group_id from groups_users where user_id=?");
        $stmt_getGroup->bind_param('i', $_SESSION['id']);
        $stmt_getGroup->execute();
        $stmt_getGroup->bind_result($user_group_id);
        $user_group_ids = array();
        while ($stmt->fetch()) {
            array_push($user_group_ids,$user_group_id);
        }
        $stmt_getGroup->close();
        // current user does not belong to the group of this event
        if (!in_array($group_id, $user_group_ids)) {
            echo json_encode(array(
                "success" => false,
                "message" => "You are not allowed to edit this event"
            ));
            exit();
        }
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
        exit();
    }
    // update personal / shared event
    if($user_id){
        $stmt_updatePersonal = $mysqli->prepare("UPDATE events SET year=?, month=?, date=?, hour=?, minute=?, title=?, description=?, tag_id=? WHERE id=? AND user_id=?");
        $stmt_updatePersonal->bind_param('iiiiissiii', $year, $month, $date,$hour,$minute,$title,$description,$tag, $event_id, $user_id);
        $stmt_updatePersonal->execute();
        $stmt_updatePersonal->close();

        // update shared copies of this event
        $stmt_updateShared = $mysqli->prepare("UPDATE events SET year=?, month=?, date=?, hour=?, minute=?, title=?, description=?, tag_id=? WHERE original_id=? AND author_id=?");
        $stmt_updateShared->bind_param('iiiiissiii', $year, $month, $date,$hour,$minute,$title,$description,$tag,$event_id,$author_id);
        $stmt_updateShared->execute();
        $stmt_updateShared->close();
    }
    // update group event
    if($group_id){
        $stmt_updateGroup = $mysqli->prepare("UPDATE events SET year=?, month=?, date=?, hour=?, minute=?, title=?, description=?, tag_id=? WHERE id=? AND group_id=?");
        $stmt_updateGroup->bind_param('iiiiissiii', $year, $month, $date,$hour,$minute,$title,$description,$tag, $event_id, $user_id);
        $stmt_updateGroup->execute();
        $stmt_updateGroup->close();
    }

    // get updated event
    $stmt_loadUpdated = $mysqli->prepare("SELECT year, month, date, hour, minute, title, description, tag_id, group_id, user_id, author_id FROM events WHERE id=?");
    $stmt_loadUpdated->bind_param('i', $event_id);
    $stmt_loadUpdated->execute();
    $stmt_loadUpdated->bind_result($year, $month, $date, $hour, $minute, $title, $description, $tag_id, $group_id, $user_id, $author_id);
    $stmt_loadUpdated->fetch();
    $stmt_loadUpdated->close();

    //get author name
    $stmt = $mysqli->prepare("select username from users where id=?");
    $stmt->bind_param('i', $author_id);
    $stmt->execute();
    $stmt->bind_result($author);
    $stmt->fetch();
    $stmt->close();
    // get group name
    if($group_id) {
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
    $shared = $user_id == $author_id? false:true;

    echo json_encode(array(
        "success" => true,
        "year" => $year,
        "month" =>$month,
        "date"=>$date,
        "hour"=>$hour,
        "minute"=>$minute,
        "title"=>htmlentities($title),
        "description"=>nl2br(htmlentities($description)),
        "user_id"=>$user_id,
        "group"=>htmlentities($group),
        "author"=>htmlentities($author),
        "tag"=>htmlentities($tag),
        "shared" => $shared
    ));

    // todo:add group add events edit events share events CSRF
    //todo:make sure user is logged in
}

?>