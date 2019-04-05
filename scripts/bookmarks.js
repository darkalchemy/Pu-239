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
            private: this.dataset.private
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
            } else if (data['content'] === 'private') {
                if (data['bookmark'] === 'yes') {
                    $(this).html('<i class=\'icon-key icon has-text-success\'></i>');
                } else {
                    $(this).html('<i class=\'icon-users icon has-text-danger\'></i>');
                }
                $(this).tooltipster('content', data['text']);
                $(this).attr('data-tid', data['tid']);
            } else if (data['content'] === 'added') {
                $(this).html('<i class=\'icon-bookmark-empty icon has-text-danger\'></i>');
                $(this).tooltipster('content', data['text']);
                $(this).attr('data-tid', data['tid']);
            } else if (data['content'] === 'deleted') {
                if (data['remove'] === 'true') {
                    $(this).closest('tr').remove();
                } else {
                    $(this).html('<i class=\'icon-bookmark-empty icon has-text-success\'></i>');
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
