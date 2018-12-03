$('.show_in_navbar').on('click', function () {
    $.ajax({
        url: './ajax/show_in_navbar.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            id: this.dataset.id,
            csrf: this.dataset.csrf,
            show: this.dataset.show
        },
        success: function (data) {
            if (data['show_in_navbar'] === 'csrf') {
                $(this).tooltipster('content', 'Invalid CSRF, try refreshing the page.');
            } else if (data['show_in_navbar'] === 'class') {
                $(this).tooltipster('content', 'Incorrect class to change this.');
            } else if (data['show_in_navbar'] === 'invalid') {
                $(this).tooltipster('content', 'Invalid data received, try refreshing the page.');
            } else if (data['show_in_navbar'] === 'fail') {
                $(this).tooltipster('content', 'Unknown failure. Try refreshing the page.');
            } else if (data['show_in_navbar'] > 0) {
                $(this).removeClass('has-text-info').addClass('has-text-success');
                $(this).html('true');
                $(this).tooltipster('content', 'Hide from Navbar');
                $(this).attr('data-show', 1);
            } else {
                $(this).removeClass('has-text-success').addClass('has-text-info');
                $(this).html('false');
                $(this).tooltipster('content', 'Showin Navbar');
                $(this).attr('data-show', 0);
            }
        }
    });
});
