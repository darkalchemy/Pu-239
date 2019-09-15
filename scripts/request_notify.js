$('.request_notify').on('click', function () {
    $.ajax({
        url: './ajax/request_notify.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            id: this.dataset.id,
            notified: this.dataset.notified
        },
        success: function (data) {
            var el = document.querySelector('#notify_' + this.dataset.id);
            if (data['notify'] === 'invalid') {
                $(this).html('?');
                $(this).tooltipster('content', 'Invalid data received, try refreshing the page.');
            } else if (data['notify'] === 'fail') {
                $(this).html('?');
                $(this).tooltipster('content', 'Unknown failure. Try refreshing the page.');
            } else if (data['notify'] > 0) {
                $(this).attr('data-notified', 1);
                $(this).html("<i class='icon-mail icon has-text-success is-marginless' aria-hidden='true'></i>");
                $(this).tooltipster('content', 'You will be notified when this has been uploaded.');
            } else {
                $(this).attr('data-notified', 0);
                $(this).html("<i class='icon-envelope-open-o icon has-text-info is-marginless' aria-hidden='true'></i>");
                $(this).tooltipster('content', 'You will NOT be notified when this has been uploaded.');
            }
        }
    });
});
