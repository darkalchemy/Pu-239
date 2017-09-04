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
            $('#help').slideToggle(1000, function() {
            });
        });
    };

    if (typeof(Storage) !== 'undefined') {
        $('.flipper').click(function(e) {
            $(this).next().slideToggle(1000, function() {
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

    if ($('#navigation').length) {
        ddsmoothmenu.init({
            mainmenuid: 'navigation',
            orientation: 'h',
            classname: 'container navigation',
            contentsource: 'markup'
        });
    };

    if ($('#platform-menu').length) {
        ddsmoothmenu.init({
            mainmenuid: 'platform-menu',
            orientation: 'h',
            classname: 'container platform-menu',
            contentsource: 'markup'
        });
    };

    var offset = 250;
    var duration = 1250;
    $(window).scroll(function() {
        if ($(this).scrollTop() > offset) {
            $('.back-to-top').fadeIn(duration);
        } else {
            $('.back-to-top').fadeOut(duration);
        }
    });

    $('.back-to-top').click(function(event) {
        event.preventDefault();
        $('html, body').animate({scrollTop: 0}, duration, 'swing');
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
        setupDependencies('upload');
    };

});
