var animate_duration = 1e3;
var animation = "fade";
var delay = 500;

$(function() {
    $(".tooltipper").tooltipster({
        theme: "tooltipster-borderless",
        side: "top",
        animation: animation,
        animationDuration: animate_duration,
        delay: delay,
        arrow: true,
        contentAsHTML: true,
        maxWidth: 500
    });
    initAll();
});

function initAll() {
    $(".dt-tooltipper.tooltipstered").tooltipster("destroy");
    $(".dt-tooltipper-large").tooltipster({
        theme: "tooltipster-borderless",
        interactive: true,
        animation: animation,
        animationDuration: animate_duration,
        delay: delay,
        arrow: true,
        contentAsHTML: true,
        maxWidth: 500,
        trigger: "custom",
        triggerOpen: {
            mouseenter: true,
            touchstart: true
        },
        triggerClose: {
            mouseleave: true,
            originClick: true,
            click: true,
            scroll: true,
            tap: true,
            touchLeave: true
        }
    });
    $(".dt-tooltipper-small").tooltipster({
        theme: "tooltipster-borderless",
        side: "top",
        interactive: false,
        animation: animation,
        animationDuration: animate_duration,
        delay: delay,
        arrow: true,
        contentAsHTML: true,
        maxWidth: 250
    });
    $(".tooltipper-ajax.tooltipstered").tooltipster("destroy");
    $(".tooltipper-ajax").tooltipster({
        trigger: "custom",
        triggerOpen: {
            mouseenter: true,
            touchstart: true
        },
        triggerClose: {
            mouseleave: true,
            originClick: true,
            click: true,
            scroll: true,
            tap: true,
            touchLeave: true
        },
        theme: [ "tooltipster-borderless", "tooltipster-custom" ],
        contentAsHTML: true,
        interactive: true,
        animation: animation,
        animationDuration: animate_duration,
        delay: delay,
        updateAnimation: animation,
        arrow: true,
        minWidth: 250,
        content: "patience, grasshopper...",
        functionBefore: function(instance, helper) {
            var $origin = $(helper.origin);
            if ($origin.data("loaded") !== true) {
                $.post("../ajax/ajax_tooltips.php", {
                    csrf_token: csrf_token
                }, function(data) {
                    if (instance.content() === "") return false;
                    instance.content(data);
                    $origin.data("loaded", true);
                });
            }
        }
    });
}
