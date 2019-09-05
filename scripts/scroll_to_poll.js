window.addEventListener('load', function(){
    let headerHeight = $('#navbar').outerHeight() + 10;
    let target = '#poll';
    let scrollToPosition = $(target).offset().top - headerHeight;
    $('html, body').animate({
        scrollTop: scrollToPosition
    }, animate_duration, 'swing');
    location.hash = '#poll';
});
