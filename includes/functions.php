<?php

function redirect_to($new_location = null) {
    if (isset($new_location)) {
        header("Location: {$new_location}");
        exit;
    }
}

function create_file($filename, $chmod = 644) {
    fopen($filename, 'w');
    chmod($filename, $chmod);
}

function get_json($url, $to_array = true) {
    if (!file_exists($url)) create_file($url, 0600);

    $json_content = file_get_contents($url);

    // Convert json data into an array
    if ($to_array) $json_content = json_decode($json_content, true);

    return $json_content;
}

function put_json($url, $content, $append = false, $from_array = true) {
    if (!file_exists($url)) create_file($url, 0600);

    if ($from_array && $append) {
        $prev = get_json($url);

        $prev[] = $content;

        $content = $prev;
        $content = json_encode($content, JSON_UNESCAPED_SLASHES);
    } elseif ($from_array && !$append) {
        $content = json_encode($content, JSON_UNESCAPED_SLASHES);
    }

    if (file_put_contents($url, $content)) return true;

    return false;
}

function activity_log($datetime, $user, $activity) {
    // Delete the log if the last message was more than 14 days ago
    if (message(last_id("message"), "unix_time") < strtotime('-14 days')) {
        unlink(ACTIVITY_LOG);
    }

    $activity = date("[D M d h:i:s A]", $datetime) . " " . $user . " " . $activity. "\n";
    $handle = fopen(ACTIVITY_LOG, 'a');

    fwrite($handle, $activity);
}

function last_id($case) {
    if (!empty($case)) {
        switch ($case) {
            case "user":
                $users = get_json(USERS_JSON);
                $last = (count($users)) ? end($users) : false;
                return ($last) ? (int)$last["user_id"] : false;
            case "convo":
                $convos = get_json(CONVOS_JSON);
                $last = (count($convos)) ? end($convos) : false;
                return ($last) ? (int)$last["convo_id"] : false;
            case "message":
                $messages = get_json(MESSAGES_JSON);
                $last = (count($messages)) ? end($messages) : false;
                return ($last) ? (int)$last["message_id"] : false;
        }
    }

    return false;
}

function good_time($unix_time) {
    if ($unix_time >= strtotime("-60 minutes")) {
        if ($unix_time >= strtotime("-1 minute")) {
            $seconds = (time() - $unix_time);

            if ($seconds == 0) return "right now";

            return ($seconds == 1) ? $seconds." second ago" : $seconds." seconds ago";
        }

        $minutes = floor((time() - $unix_time) / 60);
        return ($minutes == 1) ? $minutes." minute ago" : $minutes." minutes ago";
    } elseif ($unix_time >= strtotime("-1 day")) {
        $hours = floor((time() - $unix_time) / 3600);
        return ($hours == 1) ? $hours." hour ago" : $hours." hours ago";
    } else {
        return date("n/j/y g:i:s a", $unix_time);
    }
}

function user_exists($username, $user_id = 0) {
    $users = get_json(USERS_JSON);

    if (count($users)) {
        foreach ($users as $user) {
            if ($user["username"] === $username || $user["user_id"] === (int)$user_id) return true;
        }
    }

    return false;
}

function create_user($username, $full_name, $password) {
    if (!user_exists($username)) {
        $username = trim($username);
        $full_name = ucwords(trim($full_name));
        $user_id = last_id("user") + 1;
        $date = time();
        $colour = "#000000";

        $salt = "$2y$10$";

        for ($i = 0; $i < 22; $i++) {
            $salt .= substr("./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789", mt_rand(0, 63), 1);
        }

        $hash = crypt($password, $salt);

        $user_info = array(
            "user_id"       => $user_id,
            "username"      => $username,
            "full_name"     => $full_name,
            "password_hash" => $hash,
            "date_joined"   => $date,
            "photo"         => "",
            "colour"        => $colour
        );

        if (put_json(USERS_JSON, $user_info, true)) {
            activity_log(time(), "New user created: ".$full_name." - ".$username, null);

            return true;
        }
    }

    return false;
}

function delete_user($id, $username) {
    $users = get_json(USERS_JSON);

    if (user_exists($username)) {
        foreach ($users as $key => $user) {
            if ($user["user_id"] === $id || $user["username"] === $username) {
                unset($users[$key]);
                break;
            }
        }

        activity_log(time(), "User deleted: ".user($username, null, "full_name")." - ".$username, null);

        if (put_json(USERS_JSON, $users)) return true;
    }

    return false;
}

// Get user info
function user($username = "", $user_id, $output) {
    $users = get_json(USERS_JSON);

    foreach ($users as $user) {
        if ($user["user_id"] === $user_id || $user["username"] === $username) {
            switch ($output) {
                case "id":
                    return $user["user_id"];
                case "date_joined":
                    return $user["date_joined"];
                case "password_hash":
                    return $user["password_hash"];
                case "full_name":
                    return $user["full_name"];
                case "username":
                    return $user["username"];
                case "first_name":
                    return strstr($user["full_name"], " ", true);
                case "last_name":
                    return strstr($user["full_name"], " ", false);
                case "first_w_init":
                    $full_name = explode(" ", $user["full_name"]);

                    return $full_name[0]." ".substr($full_name[1], 0, 1);
                case "photo":
                    return $user["photo"];
                case "colour":
                    return $user["colour"];
            }
        }
    }

    return false;
}

function login($username, $password) {
    if (user_exists($username)) {
        $hash = user($username, null, "password_hash");

        if (crypt($password, $hash) === $hash) {
            $_SESSION["username"] = $username;
            $_SESSION["user_id"]  = user($username, null, "id");

            activity_log(time(), user($username, null, "full_name"), "logged in.");

            return true;
        }
    }

    return false;
}

function is_admin() {
    if (isset($_SESSION["username"]) && user($_SESSION["username"], null, "id") === $_SESSION["user_id"]) {
        return true;
    }

    return false;
}

function logout() {
    activity_log(time(), user($_SESSION["username"], null, "full_name"), "logged out.\n");

    session_unset();
    session_destroy();
}

function is_creator($convo_id) {
    $convos = get_json(CONVOS_JSON);

    foreach ($convos as $convo) {
        if ($convo["convo_id"] === (int)$convo_id) {
            if ($convo["creator"] === $_SESSION["user_id"]) return true;
            return false;
        }
    }

    return false;
}

function is_author($message_id) {
    $messages = get_json(MESSAGES_JSON);

    foreach ($messages as $message) {
        if ($message["message_id"] === (int)$message_id) {
            if ($message["user_id"] === $_SESSION["user_id"]) return true;
            return false;
        }
    }

    return false;
}


function is_invited($convo_id) {
    $convos = get_json(CONVOS_JSON);

    foreach ($convos as $convo) {
        if ($convo["convo_id"] === (int)$convo_id) {
            if (in_array($_SESSION["user_id"], $convo["users"])) return true;
            return false;
        }
    }

    return false;
}

function convo_exists($id) {
    $convos = get_json(CONVOS_JSON);

    if (count($convos)) {
        foreach ($convos as $convo) {
            if ($convo["convo_id"] === (int)$id) return true;
        }
    }

    return false;
}

function create_convo($subject, $users = array()) {
    $subject = trim($subject);
    $date_start = time();
    $convo_id = last_id("convo") + 1;

    $creator = $_SESSION["user_id"];

    $valid_users = array();

    if (is_array($users) && count($users)) {
        foreach ($users as $key => $user) {
            if (!user_exists($user)) {
                unset($users[$key]);
            } else {
                $valid_users[] = user($user, null, "id");
            }
        }
    } else {
        $valid_users[] = $_SESSION["user_id"];
    }

    if (!empty($subject)) {
        $conversation = array(
            "convo_id"   => $convo_id,
            "subject"    => $subject,
            "date_start" => $date_start,
            "creator"    => $creator,
            "users"      => $valid_users
        );

        if (put_json(CONVOS_JSON, $conversation, true)) {
            activity_log(time(), user(null, $creator, "full_name"), "created a new conversation: \"".$subject."\"");
            return true;
        }
    }

    return false;
}

function delete_convo($convo_id) {
    $convos = get_json(CONVOS_JSON);
    $messages = get_json(MESSAGES_JSON);

    if (convo_exists($convo_id)) {
        foreach ($convos as $key => $convo) {
            if ($convo["convo_id"] === (int)$convo_id) {
                if (is_creator($convo_id)) {
                    foreach ($messages as $m_key => $message) {
                        if ($message["convo_id"] === (int)$convo_id) {
                            unset($messages[$m_key]);
                        }
                    }

                    unset($convos[$key]);
                }
            }
        }

        $convos = array_values($convos);
        $messages = array_values($messages);

        activity_log(time(), user($_SESSION["username"], null, "full_name"), "deleted a conversation: \"".convo($convo_id, "subject")."\"");

        if (put_json(CONVOS_JSON, $convos, false) && put_json(MESSAGES_JSON, $messages, false)) return true;
    }

    return false;
}

function join_convo($convo_id, $user_id) {
    $convos = get_json(CONVOS_JSON);

    if (convo_exists($convo_id) && user_exists(null, $user_id) && is_creator($convo_id)) {
        foreach ($convos as $key => $convo) {
            if ($convo["convo_id"] === $convo_id) {
                if (!in_array($user_id, $convos[$key]["users"])) {
                    $convos[$key]["users"][] = $user_id;
                    if (put_json(CONVOS_JSON, $convos, false)) {
                        activity_log(time(), user($_SESSION["username"], null, "full_name"), "invited ".user(null, $user_id, "full_name")." to a conversation: \"".convo($convo_id, "subject")."\"");

                        return true;
                    }
                }

                return false;
            }
        }
    }

    return false;
}

function leave_convo($convo_id, $user_id) {
    $convos = get_json(CONVOS_JSON);

    if (convo_exists($convo_id) && user_exists(null, $user_id) && is_creator($convo_id)) {
        foreach ($convos as $key => $convo) {
            if ($convo["convo_id"] === $convo_id) {
                if (count($convos[$key]["users"]) && in_array($user_id, $convos[$key]["users"])) {
                    $key_to_unset = array_search($user_id, $convos[$key]["users"]);
                    $new_users = array();

                    unset($convos[$key]["users"][$key_to_unset]);

                    foreach ($convos[$key]["users"] as $user) {
                        $new_users[] = $user;
                    }

                    $convos[$key]["users"] = $new_users;

                    if (put_json(CONVOS_JSON, $convos, false)) {
                        activity_log(time(), user($_SESSION["username"], null, "full_name"), "kicked ".user(null, $user_id, "full_name")." from a conversation: \"".convo($convo_id, "subject")."\"");

                        return true;
                    }
                }

                return false;
            }
        }
    }

    return false;
}

function convo($id, $output) {
    $convos = get_json(CONVOS_JSON);

    if (convo_exists($id)) {
        foreach ($convos as $convo) {
            if ($convo["convo_id"] === (int)$id) {
                switch ($output) {
                    case "id":
                        return $user["convo_id"];
                    case "subject":
                        return $convo["subject"];
                    case "users":
                        $user_ids = $convo["users"];
                        $users = array();

                        foreach ($user_ids as $user_id) {
                            $users[] = user(null, $user_id, "full_name");
                        }

                        asort($users);

                        return join(", ", $users);
                    case "last_time":
                        $messages = get_json(MESSAGES_JSON);
                        $dates = array();

                        foreach ($messages as $message) {
                            if ($message["convo_id"] === (int)$convo["convo_id"]) {
                                $dates[] = $message["time"];
                            }
                        }

                        if (count($dates)) {
                            asort($dates);
                            return good_time(end($dates));
                        }

                        return "no messages";
                }
            }
        }
    }

    return false;
}

function get_convos() {
    $convos = get_json(CONVOS_JSON);
    $sorted_dates = array();
    $convo_count = 0;

    asort($convos);

    foreach ($convos as $convo) {
        if (in_array($_SESSION["user_id"], $convo["users"])) {
            $id = $convo["convo_id"];
            $subject = $convo["subject"];
            $users = array();

            $convo_count++;

            foreach ($convo["users"] as $user_id) {
                $users[] = user(null, $user_id, "full_name");
            }

            asort($users);
            $users = join(", ", $users);

            echo "<article class=\"conversation\" data-id=\"$id\">";
            echo "<div class=\"convo-subject\"><a href=\"".HOME."convo-$id\">$subject</a></div>";
            echo (is_creator($id)) ? "<a class=\"delete-convo\" href=\"".BASE_URL."delete_convo.php?id=".$id."\">Delete</a>" : "";
            echo "<div class=\"convo-users\">$users</div>";
            echo "<div class=\"convo-last-date\">".convo($id, "last_time")."</div>";
            echo "</article>";
        }
    }

    if (!$convo_count) {
        echo "<div class=\"no-convos\">No conversations.</div>";
    }
}

function create_message($convo_id, $user_id, $content, $status = false) {
    $content = nl2br(htmlentities(trim($content)));
    $regex_url = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

    // Convert any links into anchors using regex
    if (preg_match($regex_url, $content, $url)) {
        $content = preg_replace($regex_url, "<a href=\"".$url[0]."\" target=\"_blank\">".$url[0]."</a>", $content);
    }

    $status = ($status) ? 1 : 0;

    $time = time();
    $message_id = last_id("message") + 1;

    $message = array(
        "message_id" => $message_id,
        "convo_id"   => $convo_id,
        "user_id"    => $user_id,
        "time"       => $time,
        "content"    => $content,
        "status"     => $status
    );

    if (put_json(MESSAGES_JSON, $message, true)) return $message;

    return false;
}

function message($message_id, $output) {
    $messages = get_json(MESSAGES_JSON);

    foreach ($messages as $message) {
        if ($message["message_id"] === (int)$message_id) {
            switch ($output) {
                case "id":
                    return $message["message_id"];
                case "convo_id":
                    return $message["convo_id"];
                case "user_id":
                    return $message["user_id"];
                case "time":
                    return $message["time"];
                case "content":
                    return $message["content"];
                case "good_time":
                    return good_time($message["time"]);
                case "unix_time":
                    return $message["time"];
            }
        }
    }

    return false;
}

function delete_message($message_id) {
    $messages = get_json(MESSAGES_JSON);

    foreach ($messages as $key => $message) {
        if ($message["message_id"] === (int)$message_id) {
            unset($messages[$key]);
        }
    }

    $messages = array_values($messages);

    if (put_json(MESSAGES_JSON, $messages, false)) return true;

    return false;

}

function get_messages($convo_id) {
    $messages = get_json(MESSAGES_JSON);

    foreach ($messages as $message) {
        if ($message["convo_id"] === (int)$convo_id) {
            if ($message["status"]) {
                echo "<article class=\"message status\" data-id=\"".$message["message_id"]."\" data-time=\"".$message["time"]."\">";
                echo "<div class=\"message-content\">".$message["content"]."</div>";
                echo "<div class=\"message-date\">".message($message["message_id"], "good_time")."</div>";
                echo "</article>";
            } else {
                echo "<article class=\"message", (is_author($message["message_id"])) ? " me" : " other", "\" data-id=\"".$message["message_id"]."\" data-time=\"".$message["time"]."\">";
                echo "<img class=\"user-photo\" src=\"".user(null, $message["user_id"], "photo")."\" attr=\"".user(null, $message["user_id"], "full_name")."\" title=\"".user(null, $message["user_id"], "full_name")."\">";
                echo "<div class=\"message-content\">".$message["content"]."</div>";
                echo "<div class=\"message-date\">", (is_author($message["message_id"])) ? "<span data-id=\"".$message["message_id"]."\" class=\"delete-message\">Delete</span>" : "", message($message["message_id"], "good_time")."</div>";
                echo "</article>";
            }

        }
    }

    return false;
}

function is_new_message($convo_id, $last_message_time) {
    $messages = get_json(MESSAGES_JSON);
    $new_messages = array();

    foreach ($messages as $message) {
        if ($message["convo_id"] === (int)$convo_id) {
            if ($message["time"] > $last_message_time && $message["user_id"] != $_SESSION["user_id"]) {
                return true;
            }
        }
    }

    return false;
}

function get_new_message($convo_id, $last_message_time) {
    $messages = get_json(MESSAGES_JSON);
    $new_messages = array();

    foreach ($messages as $message) {
        if ($message["convo_id"] === (int)$convo_id) {
            if ($message["time"] > $last_message_time && $message["user_id"] != $_SESSION["user_id"]) {
                $new_messages[] = $message;
            }
        }
    }

    return $new_messages;
}

function get_earlier_messages() {
    //
}

// TODO: Delete a message with ajax, animate out.

function get_welcome() {
    $greetings = array("Howdy", "Welcome", "Hello", "Hi");

    echo "<div class=\"welcome-user\">".$greetings[rand(0, count($greetings) - 1)].", ".user(null, $_SESSION["user_id"], "full_name").".</div>";
}

?>
