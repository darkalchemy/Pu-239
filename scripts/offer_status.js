$('.offer_status').on('click', function () {
    $.ajax({
        url: './ajax/offer_status.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            id: this.dataset.id,
            status: this.dataset.status
        },
        success: function (data) {
            var el = document.querySelector('#status_' + this.dataset.id);
            if (data['status'] === 'invalid') {
                $(this).html('?');
                $(this).tooltipster('content', 'Invalid data received, try refreshing the page.');
            } else if (data['status'] === 'fail') {
                $(this).html('?');
                $(this).tooltipster('content', 'Unknown failure. Try refreshing the page.');
            } else if (data['status'] === 'pending') {
                $(this).attr('data-status', 'pending');
                $(this).html("<i class='icon-thumbs-down icon is-marginless' aria-hidden='true'></i>");
                $(this).tooltipster('content', 'This offer is still pending.');
            } else if (data['status'] === 'approved') {
                $(this).attr('data-status', 'approved');
                $(this).html("<i class='icon-thumbs-up icon has-text-success is-marginless' aria-hidden='true'></i>");
                $(this).tooltipster('content', 'This offer has been approved.');
            } else {
                $(this).attr('data-status', 'denied');
                $(this).html("<i class='icon-thumbs-down icon is-marginless has-text-danger' aria-hidden='true'></i>");
                $(this).tooltipster('content', 'This offer has been denied.');
            }
        }
    });
});
