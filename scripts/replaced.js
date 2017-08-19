function themes() {
    window.open('take_theme.php','My themes','height=150,width=200,resizable=no,scrollbars=no,toolbar=no,menubar=no');
}

function language_select() {
    window.open('take_lang.php','My language','height=150,width=200,resizable=no,scrollbars=no,toolbar=no,menubar=no');
}
function radio() {
    window.open('radio_popup.php','My Radio','height=700,width=800,resizable=no,scrollbars=no,toolbar=no,menubar=no');
}

function showSlidingDiv() {
    $('#slidingDiv').animate({'height': 'toggle'}, { duration: 1000 });
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
    if (s<10) {s='0' + s}
    if (m<10) {m='0' + m}
    if (h>12) {h-=12;am_pm = 'PM'}
    else {am_pm='AM'}
    if (h<10) {h='0' + h}
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

    if (pic.src == bu + '/pic/plus.gif') {
        pic.src = bu + '/pic/minus.gif';
        form.value = 'minus';
    } else {
        pic.src = bu + '/pic/plus.gif';
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

    $('span[id*=mlike]').like239({
        times : 5,              // times checked
        disabled : 5,           // disabled from liking for how many seconds
        time  : 5,              // period within check is performed
        url : '/ajax.like.php'
    });
});
