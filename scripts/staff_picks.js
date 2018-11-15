$('.staff_pick').on('click', function () {
    $.ajax({
        url: './ajax/staff_picks.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            id: this.dataset.id,
            csrf: this.dataset.csrf,
            pick: this.dataset.pick
        },
        success: function (data) {
            if (data['staff_pick'] === 'csrf') {
                $(this).html('CSRF');
                $(this).tooltipster('content', 'Invalid CSRF, try refreshing the page.');
            } else if (data['staff_pick'] === 'invalid') {
                $(this).html('?');
                $(this).tooltipster('content', 'Invalid data received, try refreshing the page.');
            } else if (data['staff_pick'] === 'fail') {
                $(this).html('?');
                $(this).tooltipster('content', 'Unknown failure. Try refreshing the page.');
            } else if (data['staff_pick'] > 0) {
                $(this).html('<i class=\'icon-minus icon has-text-red\'></i>');
                $(this).tooltipster('content', 'Remove from Staff Picks');
                $(this).attr('data-pick', data['staff_pick']);
            } else {
                $(this).html('<i class=\'icon-plus icon has-text-lime\'></i>');
                $(this).tooltipster('content', 'Add to Staff Picks');
                $(this).attr('data-pick', data['staff_pick']);
            }
        }
    });
});
