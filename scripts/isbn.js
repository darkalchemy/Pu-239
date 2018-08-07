$('#isbn').change(function () {
    console.log('changed');
    $.ajax({
        url: './ajax/isbn_lookup.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            csrf: this.dataset.csrf,
            isbn: this.value,
        },
        success: function (data) {
            var el1 = $('.isbn_outer');
            var el2 = $('.isbn_inner');
            if (data['staff_pick'] === 'csrf') {
                el1.addClass('bordered bg-00 margin20');
                el2.addClass('alt_bordered has-text-centered');
                el2.html('CSRF Failure');
            } else {
                el1.addClass('bordered bg-00 margin20');
                el2.addClass('alt_bordered');
                el2.html(data['content']);
            }
        }
    });
});

