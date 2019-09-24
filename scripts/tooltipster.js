let animation_duration = 500;
let animation = 'grow';
let update_animation = 'rotate';
let short = 250;
let long = 500;
let width = window.innerWidth * .75;
let maxWidth = Math.min(width, 600);

$(function () {
    $('.tooltipper').tooltipster({
        theme: 'tooltipster-borderless',
        interactive: false,
        animation: animation,
        animationDuration: animation_duration,
        delay: short,
        arrow: true,
        contentAsHTML: true,
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
        animationDuration: animation_duration,
        delay: long,
        arrow: true,
        contentAsHTML: true,
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
        interactive: true,
        animation: animation,
        animationDuration: animation_duration,
        dealy: short,
        arrow: true,
        contentAsHTML: true,
    });
    $('.dt-tooltipper-small').tooltipster({
        theme: 'tooltipster-borderless',
        interactive: false,
        animation: animation,
        animationDuration: animation_duration,
        delay: long,
        arrow: true,
        contentAsHTML: true,
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
        contentAsHTML: true,
        interactive: true,
        animation: animation,
        animationDuration: animation_duration,
        delay: long,
        updateAnimation: update_animation,
        arrow: true,
        minWidth: 250,
        maxWidth: maxWidth,
        content: 'patience, grasshopper...',
        functionBefore: function (instance, helper) {
            let $origin = $(helper.origin);
            if ($origin.data('loaded') !== true) {
                $.post('../ajax/ajax_tooltips.php', {}, function (data) {
                    if (instance.content() === '') return false;
                    instance.content(data);
                    $origin.data('loaded', true);
                });
            }
        }
    });
}
