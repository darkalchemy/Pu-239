$(window).on('load resize scroll', function (e) {
    if (document.body.clientWidth >= 1088) {
        var col1 = document.getElementById('left_column').offsetHeight;
        var col2 = document.getElementById('center_column').offsetHeight;
        var col3 = document.getElementById('right_column').offsetHeight;
        var max = Math.max(col1, col2, col3);

        var getTop = function (col) {
            var height = $(window).innerHeight();
            var columns = $('.parallax').outerHeight() - height;
            var travel = max - col;
            var scrollInterval = columns / travel;
            var scrolltop = $(window).scrollTop();
            var topOfColumns = $('.parallax').offset().top;
            var distance = scrolltop - topOfColumns;

            if (scrolltop < topOfColumns + max - height + 20) {
                return Math.floor(distance / scrollInterval);
            } else {
                return travel;
            }
        };

        var topOfColumns = $('.parallax').offset().top;
        var scrolltop = $(window).scrollTop();
        var distance = scrolltop - topOfColumns;

        if (distance > 0) {
            $('#left_column').css('margin-top', getTop(col1));
            $('#center_column').css('margin-top', getTop(col2));
            $('#right_column').css('margin-top', getTop(col3));
        } else {
            $('#left_column').css('margin-top', 0);
            $('#center_column').css('margin-top', 0);
            $('#right_column').css('margin-top', 0);
        }
    }
});
