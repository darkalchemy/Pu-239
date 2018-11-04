var count = 0;
$('#isbn').change(function () {
    var el = document.querySelector('#isbn');
    get_book(el.dataset.csrf, el.value, '');
});

if ($('#book').length) {
    var el = document.querySelector('#book');
    get_isbn(el.dataset.csrf, el.dataset.isbn, el.dataset.name, el.dataset.tid);
}

function get_isbn(csrf, isbn, name, tid) {
    count++;
    var el = document.querySelector('#isbn_outer');
    var e = document.createElement('div');
    e.classList.add('has-text-centered');
    e.innerHTML = 'Looking up "' + name + '" from Google Books, please be patient. (' + count + ')';
    el.appendChild(e);

    $.ajax({
        url: './ajax/isbn_lookup.php',
        type: 'POST',
        dataType: 'json',
        timeout: 7500,
        context: this,
        data: {
            csrf: csrf,
            isbn: isbn,
            name: name,
            tid: tid,
        },
        success: function (data) {
            if (data['fail'] === 'csrf') {
                e.innerHTML = 'CSRF Failure, try refreshing the page';
            } else if (data['fail'] === 'invalid') {
                e.innerHTML = 'Google Books Lookup Failed.';
            } else {
                e.remove();
                var node = document.createElement('div');
                node.innerHTML = data['content'];
                el.appendChild(node);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            if (textStatus === 'timeout') {
                if (count >= 8) {
                    e.innerHTML = 'AJAX Request timed out. Try refreshing the page.';
                } else {
                    e.remove();
                    get_isbn(csrf, isbn, name, tid);
                }
            } else {
                e.innerHTML = 'Another *unknown* was returned';
            }
        }
    });
}
