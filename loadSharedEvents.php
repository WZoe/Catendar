<?php
    session_start();
    // make sure user is logged in
    if (!isset($_SESSION['id'])) {
        echo json_encode(array(
            "success"=>false,
            "message"=>"user is not logged in"
        ));
    } else {
        // retrieve json data from ajax
        header("Content-Type: application/json");
        // retrieve data posted via fetch()
        $json_str = file_get_contents('php://input');
        $json_obj = json_decode($json_str, true);
        $currentYear = $json_obj['currentYear'];
        $currentMonth = $json_obj['currentMonth'];
        $prevMonth = $json_obj['prevMonth'];
        $nextMonth = $json_obj['nextMonth'];
        if($prevMonth != -1){
            $prevMonthStartDate = $json_obj['prevMonthStartDate'];
        }
        if($nextMonth != -1){
            $nextMonthEndDate = $json_obj['nextMonthEndDate'];
        }

        $personal_events = array();
        $shared_events = array();
        $group_events = array();

        // fetch daily events by time
        $mysqli = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
        // fetch shared events
        $stmt_shared = $mysqli->prepare("SELECT id, year, month, date, hour, minute, title, description, tag_id, author_id FROM events WHERE user_id=? AND author_id!=? AND year=? AND ((month=?) OR (month=? AND date>=?) OR (month=? AND date<=?)) ORDER BY hour, minute");
        $stmt_shared->bind_param('iiiiiiii', $_SESSION["id"], $_SESSION["id"], $currentYear, $currentMonth, $prevMonth, $prevMonthStartDate, $nextMonth, $nextMonthEndDate);
        $stmt_shared->execute();
        $stmt_shared->bind_result($event_id, $year, $month, $date, $hour, $minute, $title, $description, $tag_id, $author_id);
        while ($stmt_shared->fetch()) {
            // get author username
            $mysqli2 = new mysqli('ec2-54-191-166-77.us-west-2.compute.amazonaws.com', '503', '503', 'calendar');
            $stmt_author = $mysqli2->prepare("SELECT username FROM users WHERE id=?");
            $stmt_author->bind_param('i', $author_id);
            $stmt_author->execute();
            $stmt_author->bind_result($author_name);
            $stmt_author->fetch();
            $stmt_author->close();
            $dateNo = $month+"-"+$date;
            if(!array_key_exists($dateNo, $shared_events)){
                $shared_events[$dateNo] = array();
            }
            array_push($shared_events[$dateNo], array(
                "event_id"=>$event_id,
                "year"=>$year,
                "month"=>$month,
                "date"=>$date,
                "hour"=>$hour,
                "minute"=>$minute,
                "title"=>$title,
                "description"=>$description,
                "tag_id"=>$tag_id,
                "author_name"=>$author_name
            ));
        }
        $stmt_shared->close();
        
        // return daily events as json array
        echo json_encode(array(
            "success"=>true,
            "personal_events"=>$personal_events,
            "shared_events"=>$shared_events,
            "group_events"=>$group_events
        ));
    }
?>