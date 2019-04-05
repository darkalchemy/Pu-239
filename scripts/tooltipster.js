var animate_duration = 500;
var animation = 'grow';
var update_animation = 'rotate';
var short = 250;
var long = 500;
var distance = 12;
var maxWidth = window.innerWidth * .5;
var sides = ['bottom', 'top', 'right', 'left'];

$(function () {
    $('.tooltipper').tooltipster({
        theme: 'tooltipster-borderless',
        side: sides,
        interactive: false,
        animation: animation,
        animationDuration: animate_duration,
        delay: short,
        arrow: true,
        contentAsHTML: true,
        distance: distance,
        maxWidth: maxWidth
    });
    initAll();
});

function initAll() {
    $('.dt-tooltipper.tooltipstered').tooltipster('destroy');
    $('.dt-tooltipper-large').tooltipster({
        theme: 'tooltipster-borderless',
        interactive: true,
        animation: animation,
        animationDuration: animate_duration,
        delay: long,
        arrow: true,
        contentAsHTML: true,
        distance: distance,
        trigger: 'custom',
        maxWidth: maxWidth,
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
        side: sides,
        interactive: true,
        animation: animation,
        animationDuration: animate_duration,
        dealy: short,
        arrow: true,
        contentAsHTML: true,
        distance: distance
    });
    $('.dt-tooltipper-small').tooltipster({
        theme: 'tooltipster-borderless',
        side: sides,
        interactive: false,
        animation: animation,
        animationDuration: animate_duration,
        delay: long,
        arrow: true,
        contentAsHTML: true,
        distance: distance,
        maxWidth: maxWidth
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
        side: sides,
        contentAsHTML: true,
        interactive: true,
        animation: animation,
        animationDuration: animate_duration,
        delay: long,
        updateAnimation: update_animation,
        arrow: true,
        distance: distance,
        minWidth: 250,
        maxWidth: maxWidth,
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
