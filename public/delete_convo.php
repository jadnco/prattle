<?php

require_once("../includes/init.php");

$convo_id = (int)$_GET["id"];

if (!is_creator($convo_id) || !is_admin() || !convo_exists($convo_id) || empty($convo_id)) redirect_to(HOME);

if (delete_convo($convo_id)) {
    redirect_to(HOME);
}

?>