var count = 0;
$('#isbn').change(function () {
    var el = document.querySelector('#isbn');
    var len = el.value.length;
    if (len === 10 || len === 13) {
        get_isbn('./ajax/isbn_lookup.php', el.value, '');
    }
});

if ($('#book').length) {
    var el = document.querySelector('#book');
    get_isbn('./ajax/ebook_lookup.php', el.dataset.isbn, el.dataset.name, el.dataset.tid);
}

function get_isbn(url, isbn, name, tid) {
    count++;
    var el = document.querySelector('#isbn_outer');
    var e = document.createElement('div');
    e.classList.add('has-text-centered', 'padding20');
    e.innerHTML = 'Looking up "' + name + '" from Google Books, please be patient. (' + count + ')';
    el.appendChild(e);

    $.ajax({
        url: url,
        type: 'POST',
        dataType: 'json',
        timeout: 10000,
        context: this,
        data: {
            isbn: isbn,
            name: name,
            tid: tid
        },
        success: function (data) {
            if (data['fail'] === 'invalid') {
                e.innerHTML = 'Google Books Lookup Failed.';
                el.appendChild(e);
            } else {
                e.remove();
                var node = document.createElement('div');
                node.innerHTML = data['content'];
                el.appendChild(node);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (textStatus === 'timeout') {
                e.innerHTML = 'AJAX Request timed out. Try refreshing the page.';
                el.appendChild(e);
            } else {
                e.innerHTML = 'Another *unknown* was returned';
                el.appendChild(e);
            }
        }
    });
}
