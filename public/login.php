<?php require_once("../includes/init.php"); ?>
<?php if (is_admin()) redirect_to(HOME); ?>

<?php

if (isset($_POST["login_submit"])) {
    if (login($_POST["username"], $_POST["password"])) {
        redirect_to(HOME);
    } elseif (empty($_POST["username"]) || empty($_POST["password"])) {
        $error = "Please enter both fields.";
    } else {
        activity_log(time(), "Failed login attempt.", null);
        $error = "Couldn't login. Please try again.";
    }
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <base href="<?=BASE_URL?>">
    <title>Prattle - Login</title>
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <form id="login-form" method="post" action="">
        <div class="please-login">Prattle</div>
        <input class="<?=(isset($error)) ? "shake" : ""?>" type="text" name="username" placeholder="Username" value="<?=(isset($_POST["username"]) && !empty($_POST["username"])) ? $_POST["username"] : ""?>" autocomplete="off">
        <input class="<?=(isset($error)) ? "shake" : ""?>" type="password" name="password" placeholder="Password" autocomplete="off">
        <input type="submit" name="login_submit">
        <?php if (isset($error)) { ?>
        <div class="login-error"><?=$error?></div>
        <?php } ?>
    </form>
</body>
</html>