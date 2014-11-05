<?php

require_once("../includes/init.php");

if (is_admin() && isset($_POST["convo_id"]) && isset($_POST["last_message_time"])) {
    $convo_id = (int)$_POST["convo_id"];
    $last_message_time = (int)$_POST["last_message_time"];

    $messages = get_new_message($convo_id, $last_message_time);

    foreach ($messages as $key => $message) {
        $messages[$key]["good_time"] = message($message["message_id"], "good_time");
        $messages[$key]["user_photo"] = user(null, $message["user_id"], "photo");
    }

    echo json_encode($messages);
} else {
    echo 0;
}

?>