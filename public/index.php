<?php require_once("../includes/init.php"); ?>
<?php if (!is_admin()) redirect_to(HOME . "login"); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <base href="<?=BASE_URL?>">
    <title>Prattle</title>
    <meta name="author" content="Jaden Dessureault">
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="assets/js/main.js"></script>
</head>
<body>
    <a class="login-out" href="<?=(is_admin()) ? HOME."logout" : HOME."login"?>"><?=(is_admin()) ? "Logout" : "Login"?></a>
    <header id="main-header">
        <div class="wrapper">
            <div class="main-title"><a href="<?=HOME?>">Prattle</a></div>
        </div>
    </header>

    <section id="conversations">
        <div class="wrapper">
            <div class="convos wrapper">
                <?php get_convos(); ?>
            </div>
            <div class="new-convo-field">
                <input type="text" placeholder="Start a new conversation...">
                <input type="submit" value="Create"></div>
            </div>
        </div>
    </section>

    <footer id="main-footer"></footer>
    <?php get_welcome(); ?>
</body>
</html>