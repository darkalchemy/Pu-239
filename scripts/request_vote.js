$('.request_vote').on('click', function () {
    $.ajax({
        url: './ajax/request_vote.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            id: this.dataset.id,
            voted: this.dataset.voted
        },
        success: function (data) {
            var el = document.querySelector('#vote_' + this.dataset.id);
            if (data['voted'] === 'invalid') {
                $(this).html('?');
                $(this).tooltipster('content', 'Invalid data received, try refreshing the page.');
            } else if (data['voted'] === 'fail') {
                $(this).html('?');
                $(this).tooltipster('content', 'Unknown failure. Try refreshing the page.');
            } else if (data['voted'] === 'yes') {
                $(this).attr('data-voted', 'yes');
                $(this).html("<i class='icon-thumbs-up icon has-text-success is-marginless' aria-hidden='true'></i>");
                $(this).tooltipster('content', 'You support this request.');
            } else if (data['voted'] === 'no') {
                $(this).attr('data-voted', 'no');
                $(this).html("<i class='icon-thumbs-down icon has-text-danger is-marginless' aria-hidden='true'></i>");
                $(this).tooltipster('content', 'You oppose this request.');
            } else {
                $(this).attr('data-voted', 0);
                $(this).html("<i class='icon-thumbs-down icon is-marginless' aria-hidden='true'></i>");
                $(this).tooltipster('content', 'You have not voted for or against this request.');
            }
        }
    });
});
