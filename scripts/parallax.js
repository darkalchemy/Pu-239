$(window).on('load resize scroll', function (e) {
    if (document.body.clientWidth >= 1088) {
        var col1 = document.getElementById('left_column').offsetHeight;
        var col2 = document.getElementById('center_column').offsetHeight;
        var col3 = document.getElementById('right_column').offsetHeight;

        var max = Math.max(col1, col2, col3);
        if (max === col1) {
            var el1 = '#center_column';
            var el2 = '#left_column'; // longest
            var el3 = '#right_column';
            var travel1 = col1 - col2;
            var travel3 = col1 - col3;
        } else if (max === col2) {
            var el1 = '#left_column';
            var el2 = '#center_column'; // longest
            var el3 = '#right_column';
            var travel1 = col2 - col1;
            var travel3 = col2 - col3;
        } else {
            var el1 = '#left_column';
            var el2 = '#right_column'; // longest
            var el3 = '#center_column';
            var travel1 = col3 - col1;
            var travel3 = col3 - col2;
        }

        var height = $(window).innerHeight();
        var topOfColumns = $('.parallax').offset().top;
        var columns = $('.parallax').outerHeight() - height;

        var scrollInterval1 = columns / travel1;
        var scrollInterval3 = columns / travel3;

        var scrolltop = $(window).scrollTop();
        var distance = scrolltop - topOfColumns;

        var a1 = Math.ceil(distance / scrollInterval1);
        var b1 = scrolltop >= $(el1).offset().top + col1 - height;

        var a3 = Math.ceil(distance / scrollInterval3);
        var b3 = scrolltop >= $(el3).offset().top + col3 - height;

        if (scrolltop >= topOfColumns && b1 === false) {
            $(el1).css({
                '-webkit-transform': 'translate3d(0px, ' + a1 + 'px, 0px)',
                transform: 'translate3d(0px, ' + a1 + 'px, 0px)'
            })
            ;
        } else if (distance <= 0) {
            $(el1).css({
                '-webkit-transform': 'translate3d(0px, 0px, 0px)',
                transform: 'translate3d(0px, 0px, 0px)'
            });
        }
        if (scrolltop >= topOfColumns && b3 === false) {
            $(el3).css({
                '-webkit-transform': 'translate3d(0px, ' + a3 + 'px, 0px)',
                transform: 'translate3d(0px, ' + a3 + 'px, 0px)'
            });
        } else if (distance <= 0) {
            $(el3).css({
                '-webkit-transform': 'translate3d(0px, 0px, 0px)',
                transform: 'translate3d(0px, 0px, 0px)'
            });
        }
    }
});
