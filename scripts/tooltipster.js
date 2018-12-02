var animate_duration = 500;
var animation = 'grow';
var update_animation = 'rotate';
var delay = 300;
var distance = 12;

$(function () {
    $('.tooltipper').tooltipster({
        theme: 'tooltipster-borderless',
        side: ['bottom', 'top'],
        interactive: false,
        animation: animation,
        animationDuration: animate_duration,
        delay: delay,
        arrow: true,
        contentAsHTML: true,
        distance: distance
    });
    initAll();
});

function initAll() {
    $('.dt-tooltipper.tooltipstered').tooltipster('destroy');
    $('.dt-tooltipper-large').tooltipster({
        theme: 'tooltipster-borderless',
        interactive: true,
        side: ['bottom', 'top'],
        animation: animation,
        animationDuration: animate_duration,
        delay: delay,
        arrow: true,
        contentAsHTML: true,
        distance: distance,
        trigger: 'custom',
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
    $('.dt-tooltipper-links').tooltipster({
        theme: 'tooltipster-borderless',
        side: ['bottom', 'top'],
        interactive: true,
        animation: animation,
        animationDuration: animate_duration,
        delay: delay,
        arrow: true,
        contentAsHTML: true,
        distance: distance
    });
    $('.dt-tooltipper-small').tooltipster({
        theme: 'tooltipster-borderless',
        side: ['bottom', 'top'],
        interactive: false,
        animation: animation,
        animationDuration: animate_duration,
        delay: delay,
        arrow: true,
        contentAsHTML: true,
        distance: distance
    });
    $('.tooltipper-ajax.tooltipstered').tooltipster('destroy');
    $('.tooltipper-ajax').tooltipster({
        trigger: 'custom',
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
        theme: ['tooltipster-borderless', 'tooltipster-custom'],
        side: ['bottom', 'top'],
        contentAsHTML: true,
        interactive: true,
        animation: animation,
        animationDuration: animate_duration,
        delay: delay,
        updateAnimation: update_animation,
        arrow: true,
        distance: distance,
        content: 'patience, grasshopper...',
        functionBefore: function (instance, helper) {
            var $origin = $(helper.origin);
            var el = document.querySelector('#base_usermenu');
            if ($origin.data('loaded') !== true) {
                $.post('../ajax/ajax_tooltips.php', {
                    csrf_token: el.dataset.csrf
                }, function (data) {
                    if (instance.content() === '') return false;
                    instance.content(data);
                    $origin.data('loaded', true);
                });
            }
        }
    });
}
