let timeoutId = null;
window.addEventListener('load', event => {
    parallax(event);
}, true);
window.addEventListener('resize', event => {
    parallax(event);
}, true);
window.addEventListener('scroll', event => {
    parallax(event);
}, true);

function parallax(event) {
    event.preventDefault();
    if (document.body.clientWidth >= 1087) {
        var col1Name = document.getElementById('left_column');
        var col2Name = document.getElementById('center_column');
        var col3Name = document.getElementById('right_column');
        var col1 = col1Name.offsetHeight;
        var col2 = col2Name.offsetHeight;
        var col3 = col3Name.offsetHeight;
        var cols = document.getElementById('parallax');
        var topOfColumns = cols.offsetTop;
        var max = Math.max(col1, col2, col3);
        var scrolltop = window.scrollY;
        var distance = scrolltop - topOfColumns;
        if (distance > 0) {
            col1Name.style.marginTop = getTop(col1, max) + 'px';
            col2Name.style.marginTop = getTop(col2, max) + 'px';
            col3Name.style.marginTop = getTop(col3, max) + 'px';
        } else {
            col1Name.style.marginTop = '0';
            col2Name.style.marginTop = '0';
            col3Name.style.marginTop = '0';
        }
    }
}

function getTop(col, max) {
    var height = window.innerHeight;
    var cols = document.getElementById('parallax');
    var topOfColumns = cols.offsetTop;
    var columns = cols.offsetHeight - height;
    var travel = max - col;
    var scrollInterval = columns / travel;

    var scrolltop = window.scrollY;
    var distance = scrolltop - topOfColumns;


    if (scrolltop < topOfColumns + max - height + 20) {
        return Math.floor(distance / scrollInterval);
    } else {
        return travel;
    }
}
