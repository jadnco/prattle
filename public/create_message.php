<?php

require_once("../includes/init.php");

$convo_id = (int)$_POST["convo_id"];
$content = trim($_POST["content_"]);

if (strlen($content) == 0) {
    echo 0;
    exit;
}

$invite_regex = "/\A(@-invite:)(\s)(\S*)\z/";
$kick_regex = "/\A(@-kick:)(\s)(\S*)\z/";

if (preg_match($invite_regex, $content)) {
    $username = str_replace("@-invite: ", "", $content);
    $content = user(null, $_SESSION["user_id"], "full_name")." invited ".user($username, null, "full_name");

    if (join_convo($convo_id, user($username, null, "id"))) {
        if ($message = create_message($convo_id, $_SESSION["user_id"], $content, true)) {
            $message["full_name"] = user($message["user_id"], null, "full_name");
            $message["good_time"] = message($message["message_id"], "good_time");

            echo "<article class=\"message status\" data-id=\"".$message["message_id"]."\" data-time=\"".$message["time"]."\"><div class=\"status-content\">".$message["content"]."</div><div class=\"status-date\">".good_time($message["time"])."</div></article>";
            exit;
        } else {
            echo 0;
        }
    }
} elseif (preg_match($kick_regex, $content)) {
    $username = str_replace("@-kick: ", "", $content);
    $content = user(null, $_SESSION["user_id"], "full_name")." kicked ".user($username, null, "full_name");

    if (leave_convo($convo_id, user($username, null, "id"))) {
        if ($message = create_message($convo_id, $_SESSION["user_id"], $content)) {
            $message["full_name"] = user($message["user_id"], null, "full_name");
            $message["good_time"] = message($message["message_id"], "good_time");

            echo "<article class=\"message status\" data-id=\"".$message["message_id"]."\" data-time=\"".$message["time"]."\"><div class=\"status-content\">".$message["content"]."</div><div class=\"status-date\">".good_time($message["time"])."</div></article>";
            exit;
        } else {
            echo 0;
        }
    }
} else {
    if ($message = create_message($convo_id, $_SESSION["user_id"], $content)) {
        $message["full_name"] = user($message["user_id"], null, "full_name");
        $message["good_time"] = message($message["message_id"], "good_time");
        $message["user_photo"] = user(null, $message["user_id"], "photo");

        echo "<article class=\"message me\" data-id=\"".$message["message_id"]."\" data-time=\"".$message["time"]."\"><img class=\"user-photo\" src=\"".$message["user_photo"]."\" title=\"".user(null, $_SESSION["user_id"], "full_name")."\"><div class=\"message-content\">".$message["content"]."</div><div class=\"message-date\"><span data-id=\"".$message["message_id"]."\" class=\"delete-message\"></span>".good_time($message["time"])."</div></article>";
    } else {
        echo 0;
    }
}

?>