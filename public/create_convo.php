<?php

require_once("../includes/init.php");

$subject = (isset($_POST["convoSubject"]) && !empty($_POST["convoSubject"])) ? $_POST["convoSubject"] : "";

/* TODO: invite regex.. */
$invite_regex = "";

if (create_convo($subject, null)) {
    $id = last_id("convo");
    $users = convo(last_id("convo"), "users");
    $data = array("id" => $id, "subject" => $subject, "users" => $users);

    echo json_encode($data);
} else {
    echo 0;
}

?>