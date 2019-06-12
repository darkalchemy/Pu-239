var count = 0;
if ($('#descr').length) {
    var el = document.querySelector('#descr');
    get_descr(el.dataset.tid);
}

function get_descr(tid) {
    count++;
    var el = document.querySelector('#descr_outer');
    var e = document.createElement('div');
    e.classList.add('has-text-centered');
    e.innerHTML = 'Grabbing and processing all of the images in the torrent\'s description, please be patient. (' + count + ')';
    el.appendChild(e);
    $.ajax({
        url: './ajax/descr_format.php',
        type: 'POST',
        dataType: 'json',
        timeout: 7500,
        context: this,
        data: {
            tid: tid
        },
        success: function (data) {
            if (data['fail'] === 'invalid') {
                e.innerHTML = 'Invalid text in \$torrent[\'descr\'].';
                el.appendChild(e);
            } else {
                e.remove();
                var node = document.createElement('div');
                node.innerHTML = data['descr'];
                el.appendChild(node);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            if (textStatus === 'timeout') {
                if (count >= 8) {
                    e.innerHTML = 'AJAX Request timed out. Try refreshing the page.';
                    el.appendChild(e);
                } else {
                    e.remove();
                    get_descr(tid);
                }
            } else {
                e.innerHTML = 'Another *unknown* was returned';
                el.appendChild(e);
            }
        }
    });
}
