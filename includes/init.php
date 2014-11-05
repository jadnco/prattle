<?php

// Create new constants for dependent json files
define("USERS_JSON", "../users.json");
define("CONVOS_JSON", "../convos.json");
define("MESSAGES_JSON", "../messages.json");

define("ACTIVITY_LOG", "../activity.log");

// Get ROOT_PATH by calling __DIR__ (without /public)
define("ROOT_PATH", "/Applications/XAMPP/xamppfiles/htdocs/prattle");
define("INC_PATH", ROOT_PATH . "/includes/");

// Base url (public folder); eg. http://example.com/public
define("BASE_URL", "http://localhost/prattle/public");

// Add the home url; eg. http://example.com
define("HOME", "http://localhost/prattle");

// Timezone
date_default_timezone_set("America/Winnipeg");

session_start();

// All the main functions
require_once("functions.php");

if (!file_exists(USERS_JSON)) create_file(USERS_JSON, 0600);
if (!file_exists(CONVOS_JSON)) create_file(CONVOS_JSON, 0600);
if (!file_exists(MESSAGES_JSON)) create_file(MESSAGES_JSON, 0600);
if (!file_exists(ACTIVITY_LOG)) create_file(ACTIVITY_LOG, 0600);

?>
