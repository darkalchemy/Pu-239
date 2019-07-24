$('.ignore_image').on('click', function () {
    $.ajax({
        url: './ajax/ignore_image.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            id: this.dataset.id,
            pick: this.dataset.pick
        },
        success: function (data) {
            var el = document.querySelector('#ignore_image' + this.dataset.id);
            if (data['ignore_image'] === 'invalid') {
                $(this).html('?');
                $(this).tooltipster('content', 'Invalid data received, try refreshing the page.');
            } else if (data['ignore_image'] === 'fail') {
                $(this).html('?');
                $(this).tooltipster('content', 'Unknown failure. Try refreshing the page.');
            } else if (data['ignore_image'] > 0) {
                $(this).html('<i class="icon-star-empty icon has-text-danger"></i>');
                $(this).tooltipster('content', 'Remove from Staff Picks');
                $(this).attr('data-pick', data['staff_pick']);
                el.innerHTML = '<img src="./images/staff_pick.png" class="tooltipper emoticon is-2x" alt="Staff Pick!" title="Staff Pick!">';
            } else {
                $(this).html('<i class="icon-star-empty icon has-text-success"></i>');
                $(this).tooltipster('content', 'Add to Staff Picks');
                $(this).attr('data-pick', data['staff_pick']);
                el.innerHTML = '';
            }
        }
    });
});
