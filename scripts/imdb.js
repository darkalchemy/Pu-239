$('#url').change(function () {
    $.ajax({
        url: './ajax/imdb_lookup.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            csrf: this.dataset.csrf,
            url: this.value,
        },
        success: function (data) {
            var el1 = $('.imdb_outer');
            var el2 = $('.imdb_inner');
            if (data['staff_pick'] === 'csrf') {
                el1.addClass('bordered bg-00 margin20');
                el2.addClass('alt_bordered has-text-centered');
                el2.html('CSRF Failure');
            } else {
                el1.addClass('bordered bg-00 margin20');
                el2.addClass('alt_bordered');
                el2.html(data['content']);
                $('#poster').val(data['poster']);
            }
        }
    });
});

