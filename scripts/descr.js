if ($('#descr').length) {
    var el = document.querySelector('#descr');
    get_descr(el.dataset.csrf, el.dataset.tid);
}

function get_descr(csrf, tid) {
    var el1 = $('.descr_outer');
    var el2 = $('.descr_inner');
    el2.addClass('has-text-centered');
    el2.html("Grabbing and processing all of the images in the torrent's description, please be patient.");

    $.ajax({
        url: './ajax/descr_format.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            csrf: csrf,
            tid: tid,
        },
        success: function (data) {
            if (data['fail'] === 'csrf') {
                el2.html('CSRF Failure, try refreshing the page');
            } else if (data['fail'] === 'invalid') {
                el2.html("Invalid text in \$torrent['descr'].");
            } else {
                el2.removeClass('has-text-centered');
                el2.html(data['descr']);
            }
        }
    });
}
