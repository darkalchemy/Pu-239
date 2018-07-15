function flipBox(who) {
    var tmp;
    if (document.images['b_' + who].src.indexOf('_on') == -1) {
        tmp = document.images['b_' + who].src.replace('_off', '_on');
        document.getElementById('box_' + who).style.display = 'none';
        document.images['b_' + who].src = tmp;
    } else {
        tmp = document.images['b_' + who].src.replace('_on', '_off');
        document.getElementById('box_' + who).style.display = 'block';
        document.images['b_' + who].src = tmp;
    }
}
