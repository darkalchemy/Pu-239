var count = 0;
if ($('#tvmaze').length) {
    var el = document.querySelector('#tvmaze');
    get_tvmaze(el.dataset.csrf, el.dataset.tvmazeid, el.dataset.name, el.dataset.tid);
}

function get_tvmaze(csrf, tvmazeid, name, tid) {
    count++;
    var el = document.querySelector('#tvmaze_outer');
    var e = document.createElement('div');
    e.classList.add('has-text-centered');
    e.innerHTML = 'Looking up "' + name + '" from TVMaze, please be patient. (' + count + ')';
    el.appendChild(e);

    $.ajax({
        url: './ajax/tvmaze_lookup.php',
        type: 'POST',
        dataType: 'json',
        timeout: 7500,
        context: this,
        data: {
            csrf: csrf,
            tvmazeid: tvmazeid,
            tid: tid,
            name: name
        },
        success: function (data) {
            if (data['fail'] === 'csrf') {
                e.innerHTML = 'CSRF Failure, try refreshing the page';
            } else if (data['fail'] === 'invalid') {
                e.innerHTML = 'TVMaze Lookup Failed.';
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
                    get_tvmaze(csrf, tvmazeid, name, tid);
                }
            } else {
                e.innerHTML = 'Another *unknown* was returned';
            }
        }
    });
}
