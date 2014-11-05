<?php require_once("../includes/init.php"); ?>
<?php if (!is_admin()) redirect_to(HOME . "login"); ?>

<?php $convo_id = (isset($_GET["convo_id"])) ? $_GET["convo_id"] : ""?>
<?php if (!convo_exists($convo_id)) redirect_to(HOME); ?>
<?php if (!is_invited($convo_id)) redirect_to(HOME); ?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <base href="<?=BASE_URL?>">
    <title>Prattle - <?=convo($convo_id, "subject")?></title>
    <meta name="author" content="Jaden Dessureault">
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="viewport" content="width=device-width, user-scalable=no, minimal-ui">
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="assets/js/main.js"></script>
    <style>
        .message.me .message-content { background: <?=user($_SESSION["username"], null, "colour")?> !important; }
        .message.me .message-content:before { border-left-color: <?=user($_SESSION["username"], null, "colour")?> !important; }
    </style>
</head>
<body>
    <a class="login-out" href="<?=(is_admin()) ? HOME."logout" : HOME."login"?>"><?=(is_admin()) ? "Logout" : "Login"?></a>
    <header id="main-header">
        <div class="wrapper">
            <div class="main-title"><a href="<?=HOME?>">Prattle</a></div>
        </div>
    </header>

    <section id="main-convo" data-id="<?=$convo_id?>">
        <div class="wrapper">
            <div class="convo-subject"><?=convo($convo_id, "subject")?></div>
            <div class="chat wrapper">
                <?php get_messages($convo_id); ?>
            </div>
            <div class="new-message-field">
                <input type="text" id="message-input" placeholder="Type your message...">
                <input type="submit" value="Send"></div>
            </div>
        </div>
    </section>

    <footer id="main-footer"></footer>

    <?php get_welcome(); ?>
    <script>

        var messageCount = 0;

        $(window).on("focus", function() {
            $("title").text("Prattle - <?=convo($convo_id, "subject")?>");

            messageCount = 0;
        });

        setInterval(function(){
            getNewMessages();
        }, 1000);

        function getNewMessages() {
            var lastMessageTime = $("#main-convo .message:last-of-type").attr("data-time"),
                convoId = $("#main-convo").attr("data-id");

            $.ajax({
                url: "get_new_message.php",
                type: "POST",
                dataType: "json",
                data: {last_message_time: lastMessageTime, convo_id: convoId},
                success: function(data) {
                    if ($.isEmptyObject(data) == false) {
                        console.log(data);

                        $.each(data, function (i, value) {
                            messageCount++;
                            $("#main-convo .chat.wrapper").append("<article class=\"message other\" data-id=\""+value.message_id+"\" data-time=\""+value.time+"\"><img class=\"user-photo\" src=\""+value.user_photo+"\" attr=\""+value.full_name+"\" title=\""+value.full_name+"\"><div class=\"message-content\">"+value.content+"</div><div class=\"message-date\">"+value.good_time+"</div></article>");
                            $("#main-convo .message:last-of-type").addClass("post-animate");

                            // Scroll to the bottom of the chat
                            $("#main-convo .chat.wrapper").animate({scrollTop: $('#main-convo .chat.wrapper').prop("scrollHeight")}, 300);
                        });

                        if (!document.hasFocus()) {
                            $("title").text("("+messageCount+") "+"Prattle - <?=convo($convo_id, "subject")?>");
                        }

                    }
                }
            });
        }
    </script>
</body>
</html>