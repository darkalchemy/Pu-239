$('#isbn').change(function () {
    var el = document.querySelector('#isbn');
    get_book(el.dataset.csrf, el.value, '');
});

if ($('#book').length) {
    var el = document.querySelector('#book');
    get_isbn(el.dataset.csrf, el.dataset.isbn, el.dataset.name, el.dataset.tid);
};

function get_isbn(csrf, isbn, name, tid) {
    var el1 = $('.isbn_outer');
    var el2 = $('.isbn_inner');
    el2.addClass('has-text-centered');
    el2.html('Looking up "' + name + '" from Google Books, please be patient.');

    $.ajax({
        url: './ajax/isbn_lookup.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            csrf: csrf,
            isbn: isbn,
            name: name,
            tid: tid,
        },
        success: function (data) {
            if (data['content'] === 'csrf') {
                el1.addClass('bordered bg-00 margin20');
                el2.addClass('alt_bordered has-text-centered');
                el2.html('CSRF Failure');
            } else {
                el2.removeClass('has-text-centered');
                el2.html(data['content'][0]);
            }
        }
    });
}
