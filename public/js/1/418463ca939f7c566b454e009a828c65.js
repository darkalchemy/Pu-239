function flipBox(who) {
    var tmp;
    if (document.images["b_" + who].src.indexOf("_on") == -1) {
        tmp = document.images["b_" + who].src.replace("_off", "_on");
        document.getElementById("box_" + who).style.display = "none";
        document.images["b_" + who].src = tmp;
    } else {
        tmp = document.images["b_" + who].src.replace("_on", "_off");
        document.getElementById("box_" + who).style.display = "block";
        document.images["b_" + who].src = tmp;
    }
}

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
            $.post("ajax/member_input.php", {
                action: "flush_torrents",
                id: id
            });
            $("#success").show();
        }
        return false;
    });
});

$(document).ready(function() {
    $("#tabs").tabs();
    $("#featuredvid > ul").tabs();
});