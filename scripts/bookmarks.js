$('.bookmarks').on('click', function () {
    $.ajax({
        url: './ajax/bookmarks.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            tid: this.dataset.tid,
            csrf: this.dataset.csrf,
            remove: this.dataset.remove,
        },
        success: function (data) {
            if (data['fail'] === 'csrf') {
                $(this).html('CSRF');
                $(this).tooltipster('content', 'Invalid CSRF, try refreshing the page.');
            } else if (data['fail'] === 'invalid') {
                $(this).html('?');
                $(this).tooltipster('content', 'Invalid data received, try refreshing the page.');
            } else if (data['fail'] === 'fail') {
                $(this).html('?');
                $(this).tooltipster('content', 'Unknown failure. Try refreshing the page.');
            } else if (data['content'] === 'added') {
                $(this).html('<i class=\'icon-cancel icon has-text-danger\'></i>');
                $(this).tooltipster('content', data['text']);
                $(this).attr('data-tid', data['tid']);
            } else if (data['content'] === 'deleted') {
                if (data['remove'] === 'true') {
                    $(this).closest('tr').remove();
                } else {
                    $(this).html('<i class=\'icon-ok icon has-text-success\'></i>');
                    $(this).tooltipster('content', data['text']);
                    $(this).attr('data-tid', data['tid']);
                }
            } else {
                $(this).html('?');
                $(this).tooltipster('content', 'Unknown failure. Try refreshing the page.');
            }
        }
    });
});
