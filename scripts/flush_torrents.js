$(function() {
    $("#form").submit(function() {
        var id = $("input#id").val();
        var WhatAction = $("input#action2").val();
        if (WhatAction == "flush_torrents") {
            if (id == "") {
                $("#flush_error").fadeIn();
                $("#flush_button").hide();
                return false;
            }
            $("#flush").fadeOut();
            $("#flush_button").hide();
            $.post("./ajax/member_input.php", {
                action: "flush_torrents",
                id: id
            });
            $("#success").show();
        }
        return false;
    });
});
