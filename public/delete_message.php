<?php

require_once("../includes/init.php");

$message_id = (isset($_POST["message_id"])) ? (int)$_POST["message_id"] : "";

if (!is_author($message_id) || !is_admin() || empty($message_id)) redirect_to(HOME);

if (delete_message($message_id)) {
    echo 1;
} else {
    echo 0;
}

?>