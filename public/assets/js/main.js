$(document).ready(function() {
    $("#main-convo .new-message-field input[type='submit']").on("click", createMessage);
    $("#main-convo .message.me .delete-message").on("click", deleteMessage);
    $("#conversations .new-convo-field input[type='submit']").on("click", createConvo);

    $("#main-convo .new-message-field input[type='text']").on("focus", function() {
        $(document).keypress(function(e) {
            if(e.which == 13) {
                e.preventDefault();
                createMessage();
            }
        });
    });

    var messageVal = $("#main-convo .new-message-field input[type='text']").val();

    function createConvo() {
        var subject = $("#conversations .new-convo-field input[type='text']").val();

        $.ajax({
            url: "create_convo.php",
            type: "POST",
            data: {convoSubject: subject},
            dataType: "json",
            success: function(data) {
                // Clear the textbox
                $("#conversations .new-convo-field input[type='text']").val("");

                $("#conversations .convos.wrapper").prepend("<article class=\"conversation\" data-id=\""+data.id+"\"><div class=\"convo-subject\"><a href=\"../convo-"+data.id+"\">"+data.subject+"</a></div><a class=\"delete-convo\" href=\"\">Delete</a><div class=\"convo-users\">"+data.users+"</div><div class=\"convo-last-date\">32 seconds ago</div></article>");
                $("#conversations .conversation:first-of-type").addClass("convo-animate");

                // Scroll to the bottom of the chat
                $("#conversations .convos.wrapper").animate({scrollTop: -($("#conversations .convos.wrapper").prop("scrollHeight"))}, 200);
            },
            error: function() {
                console.log("couldn't create conversation");
            }
        });
    }

    function createMessage() {
        var content = $("#main-convo .new-message-field input[type='text']").val(),
            convoId = $("#main-convo").attr("data-id");

        // Clear the textbox
        $("#main-convo .new-message-field input[type='text']").val("");
        $("#main-convo .new-message-field input[type='text']").focus();

        if (content.trim().length > 0) {
            $.ajax({
                url: "create_message.php",
                type: "POST",
                data: {convo_id: convoId, content_: content},
                success: function(data) {
                    $("#main-convo .chat.wrapper").append(data);
                    $("#main-convo .message:last-of-type").addClass("post-animate");

                    // Scroll to the bottom of the chat
                    $("#main-convo .chat.wrapper").animate({scrollTop: $('#main-convo .chat.wrapper').prop("scrollHeight")}, 300);
                },
                error: function() {
                    console.log("couldnt send");
                }
            });
        }
    }

    function deleteMessage() {
        var messageId = $(this).attr("data-id");

        $("#main-convo .message[data-id='"+messageId+"']").addClass("to-delete");
        setTimeout(function() {
          $("#main-convo .message[data-id='"+messageId+"']").remove();
        }, 800);

        $("#main-convo .message[data-id='"+messageId+"']").prev(".message").addClass("before-delete");
        setTimeout(function() {
          $("#main-convo .message[data-id='"+messageId+"']").removeClass("before-delete");
        }, 300);

        $("#main-convo .message.before-delete ~ .message").addClass("after-delete");
        setTimeout(function() {
          $("#main-convo .message.before-delete ~ .message").removeClass("after-delete");
        }, 900);

        /* $.ajax({
            url: "delete_message.php",
            type: "POST",
            dataType: "json",
            data: {message_id: messageId},
            success: function(data) {
                $("#main-convo [data-id='"+messageId+"']").prev().addClass("before-delete");
                $("#main-convo .message.before-delete ~ .message").addClass("after-delete");
            },
            error: function() {
                console.log("couldnt delete");
            }
        }); */


    }
});