var count = 0;
$('#url').change(function () {
    var el = document.querySelector('#url');
    get_imdb(el.value);
});

if ($('#imdb').length) {
    var el = document.querySelector('#imdb');
    get_imdb(el.dataset.imdbid, el.dataset.tid, el.dataset.poster);
}

function get_imdb(url, tid, image) {
    count++;
    var el = document.querySelector('#imdb_outer');
    var e = document.createElement('div');
    e.classList.add('has-text-centered', 'padding20');
    e.innerHTML = 'Looking up "' + url + '" from IMDb, please be patient. (' + count + ')';
    el.appendChild(e);

    $.ajax({
        url: './ajax/imdb_lookup.php',
        type: 'POST',
        dataType: 'json',
        timeout: 7500,
        context: this,
        data: {
            url: url,
            tid: tid,
            image: image
        },
        success: function (data) {
           if (data['fail'] === 'invalid') {
                e.innerHTML = 'IMDb Lookup Failed. Please check that your imdb link is correct.';
            } else {
                e.remove();
                var node = document.createElement('div');
                node.innerHTML = data['content'];
                el.appendChild(node);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (textStatus === 'timeout') {
                if (count >= 8) {
                    e.innerHTML = 'AJAX Request timed out. Try refreshing the page.';
                } else {
                    e.remove();
                    get_imdb(url, tid, image);
                }
            } else {
                e.innerHTML = 'Another *unknown* was returned';
            }
        }
    });
}
