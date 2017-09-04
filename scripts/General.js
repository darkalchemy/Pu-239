jQuery.preloadImages = function () {
    for (var i = 0; i < arguments.length; i++)
        jQuery("<img>").attr("src", arguments[i]);
}
jQuery.preloadImages("./images/home.png", "./images/homeo.png", "./images/browse.png", "./images/browseo.png", "./images/search.png", "./images/searcho.png", "./images/upload.png", "./images/uploado.png", "./images/chat.png", "./images/chato.png", "./images/forum.png", "./images/forumo.png", "./images/top.png", "./images/topo.png", "./images/rules.png", "./images/ruleso.png", "./images/faq.png", "./images/faqo.png", "./images/links.png", "./images/linkso.png", "./images/staff.png", "./images/staffo.png");

jQuery(document).ready(function () {

    $("#iconbar li a").hover(
        function () {
            var iconName = $(this).children("img").attr("src");
            var origen = iconName.split(".png")[0];
            $(this).children("img").attr({src: "" + origen + "o.png"});
            $(this).css("cursor", "pointer");
            $(this).animate({width: "100px"}, {queue: false, duration: "normal"});
            $(this).children("span").animate({opacity: "show"}, "fast");
        },
        function () {
            var iconName = $(this).children("img").attr("src");
            var origen = iconName.split("o.")[0];
            $(this).children("img").attr({src: "" + origen + ".png"});
            $(this).animate({width: "57px"}, {queue: false, duration: "normal"});
            $(this).children("span").animate({opacity: "hide"}, "fast");
        });
});
