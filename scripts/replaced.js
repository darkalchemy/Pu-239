var offset = 250;
var animate_duration = 1250;
var easing = 'swing';

function themes() {
    PopUp('take_theme.php','My themes',300, 150, 1, 0);
}

function language_select() {
    PopUp('take_lang.php','My language', 300, 150, 1, 0);
}

function radio() {
    PopUp('radio_popup.php','My Radio', 800, 700, 1, 0);
}

function refrClock() {
    var d=new Date();
    var s=d.getSeconds();
    var m=d.getMinutes();
    var h=d.getHours();
    var day=d.getDay();
    var date=d.getDate();
    var month=d.getMonth();
    var year=d.getFullYear();
    var am_pm;
    if (s<10) {
        s='0' + s;
    }
    if (m<10) {
        m='0' + m;
    }
    if (h>12) {
        h-=12;am_pm = 'pm';
    } else {
        am_pm='am';
    }
    document.getElementById('clock').innerHTML=h + ':' + m + ':' + s + ' ' + am_pm;
    setTimeout('refrClock()',1000);
}

function daylight_show() {
    if (document.getElementById('tz-checkdst').checked) {
        document.getElementById('tz-checkmanual').style.display = 'none';
    } else {
        document.getElementById('tz-checkmanual').style.display = 'block';
    }
}

function togglepic(bu, picid, formid) {
    var pic = document.getElementById(picid);
    var form = document.getElementById(formid);

    if (pic.src == bu + '/images/plus.gif') {
        pic.src = bu + '/images/minus.gif';
        form.value = 'minus';
    } else {
        pic.src = bu + '/images/plus.gif';
        form.value = 'plus';
    }
}

$(function() {
    if ($('#clock').length) {
        refrClock();
    };

    if ($('#triviabox').length) {
        $('#triviabox').iFrameResize({
            enablePublicMethods: true,
        });
    };

    if ($('.password').length) {
        $('.password').pstrength();
    };

    if ($('#help_open').length) {
        $('#help_open').click(function(){
            $('#help').slideToggle(animate_duration, easing, function() {
            });
        });
    };

    if (typeof(Storage) !== 'undefined') {
        $('.flipper').click(function(e) {
            $(this).next().slideToggle(animate_duration, easing, function() {
                var id = $(this).parent().attr('id');
                if (!$(this).is(':visible')) {
                    localStorage.setItem(id, 'closed');
                } else {
                    localStorage.removeItem(id);
                }
            });
            $(this).parent().find('.fa').toggleClass('fa-angle-up fa-angle-down');
        });
    }

    $(window).scroll(function() {
        if ($(this).scrollTop() > offset) {
            $('.back-to-top').fadeIn(animate_duration);
        } else {
            $('.back-to-top').fadeOut(animate_duration);
        }
    });

    $('.back-to-top').click(function(event) {
        event.preventDefault();
        $('html, body').animate({scrollTop: 0}, animate_duration, easing);
        $('.back-to-top').blur()
        return false;
    })

    if ($('#request_form').length) {
        $('#request_form').validate();
    };

    if ($('#offer_form').length) {
        $('#offer_form').validate();
    };

    if ($('#upload_form').length) {
        setupDependencies('upload_form');
    };

    if ($('#edit_form').length) {
        setupDependencies('edit_form');
    };

    if ($('#icarousel-slides').length) {
        $('#icarousel').iCarousel({
            easing: 'ease-in-out',
            slides: 10,
            make3D: !1,
            perspective: 500,
            animationSpeed: 500,
            pauseTime: 5E3,
            startSlide: 2,
            directionNav: !0,
            autoPlay: !0,
            keyboardNav: !0,
            touchNav: !0,
            mouseWheel: true,
            pauseOnHover: !1,
            nextLabel: 'Next',
            previousLabel: 'Previous',
            playLabel: 'Play',
            pauseLabel: 'Pause',
            randomStart: !1,
            slidesSpace: '200',
            slidesTopSpace: '20',
            direction: 'rtl',
            timer: '360bar',
            timerBg: '#000',
            timerColor: '#FFF',
            timerOpacity: 0.4,
            timerDiameter: 35,
            timerPadding: 4,
            timerStroke: 3,
            timerBarStroke: 1,
            timerBarStrokeColor: '#FFF',
            timerBarStrokeStyle: 'solid',
            timerBarStrokeRadius: 4,
            timerPosition: 'top-right',
            timerX: 10,
            timerY: 10
        });
    };
    if ($('#IE_ALERT').length) {
        if (navigator.userAgent.search("MSIE") >= 0) {
            $('#IE_ALERT').slideToggle(animate_duration, easing, function() {
            });
        }
    };

    $('#hamburger').click(function(event) {
        event.preventDefault();
        $('#navbar').addClass('showNav');
        var winHeight = $(window).outerHeight();
        $('#menuWrapper').css('height',winHeight + 'px');
        $('#menuWrapper').slideToggle(animate_duration, easing, function() {
        });
    });

    $('#close').click(function(event) {
        event.preventDefault();
        $('#menuWrapper').slideToggle(animate_duration, easing, function() {
            $('#navbar').removeClass('showNav');
            $('#menuWrapper').css('height','auto');
        });
    });

    $('#menuWrapper ul li').hover( function () {
        var el = $(this).children('ul');
        // check if it has a class of .hov
        if (el.hasClass('hov')) {
            $(el).removeClass('hov');
        } else {
            $(el).addClass('hov');
        }
    });
});
