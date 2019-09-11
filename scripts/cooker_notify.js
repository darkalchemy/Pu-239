$('.cooker_notify').on('click', function () {
    $.ajax({
        url: './ajax/cooker_notify.php',
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
                $(this).html('UnNotify');
                $(this).tooltipster('content', 'UnNotify');
            } else {
                $(this).attr('data-notified', 0);
                $(this).html('Notify');
                $(this).tooltipster('content', 'Notify');
            }
        }
    });
});
