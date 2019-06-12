var count = 0;
if ($('#tvmaze').length) {
    var el = document.querySelector('#tvmaze');
    get_tvmaze(el.dataset.tvmazeid, el.dataset.name, el.dataset.tid);
}

function get_tvmaze(tvmazeid, name, tid) {
    count++;
    var el = document.querySelector('#tvmaze_outer');
    var e = document.createElement('div');
    e.classList.add('has-text-centered', 'padding20');
    e.innerHTML = 'Looking up "' + name + '" from TVMaze, please be patient. (' + count + ')';
    el.appendChild(e);

    $.ajax({
        url: './ajax/tvmaze_lookup.php',
        type: 'POST',
        dataType: 'json',
        timeout: 10000,
        context: this,
        data: {
            tvmazeid: tvmazeid,
            tid: tid,
            name: name
        },
        success: function (data) {
            if (data['fail'] === 'invalid') {
                e.innerHTML = 'TVMaze Lookup Failed.';
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
                e.innerHTML = 'No TVMaze Data found for ' + name;
                el.appendChild(e);
                console.log('failed');
            }
        }
    });
}
