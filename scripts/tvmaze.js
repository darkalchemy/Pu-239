if ($('#tvmaze').length) {
    var el = document.querySelector('#tvmaze');
    get_tvmaze(el.dataset.csrf, el.dataset.tvmazeid, el.dataset.name, el.dataset.tid);
}

function get_tvmaze(csrf, tvmazeid, name, tid) {
    var el2 = $('.tvmaze_inner');
    el2.addClass('has-text-centered');
    el2.html('Looking up "' + name + '" from TVMaze, please be patient.');

    $.ajax({
        url: './ajax/tvmaze_lookup.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            csrf: csrf,
            tvmazeid: tvmazeid,
            tid: tid,
            name: name,
        },
        success: function (data) {
            if (data['fail'] === 'csrf') {
                el2.html('CSRF Failure, try refreshing the page');
            } else if (data['fail'] === 'invalid') {
                el2.html('TVMaze Lookup Failed.');
            } else {
                el2.removeClass('has-text-centered');
                el2.html(data['content']);
                if (data['background']) {
                    document.getElementsByTagName('body')[0].style.backgroundColor = 'black';
                    document.getElementsByTagName('body')[0].style.backgroundImage = 'url(' + data['background'] + ')';
                    document.getElementsByTagName('body')[0].style.backgroundSize = 'cover';
                } else {
                    document.getElementsByTagName('body')[0].style.backgroundImage = '';
                }
            }
        }
    });
}
