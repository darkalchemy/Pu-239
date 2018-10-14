$('#url').change(function () {
    var el = document.querySelector('#url');
    get_imdb(el.dataset.csrf, el.value);
});

if ($('#imdb').length) {
    var el = document.querySelector('#imdb');
    get_imdb(el.dataset.csrf, el.dataset.imdbid, el.dataset.tid);
}

function get_imdb(csrf, url, tid) {
    var el1 = $('.imdb_outer');
    var el2 = $('.imdb_inner');
    var el3 = $('#poster');
    var el4 = $('.poster_container');
    var el5 = $('.banner_container');
    el2.addClass('has-text-centered');
    el4.addClass('padding20 margin10 round10 bg-00');
    el2.html('Looking up "' + url + '" from IMDb, please be patient.');

    $.ajax({
        url: './ajax/imdb_lookup.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            csrf: csrf,
            url: url,
            tid: tid,
        },
        success: function (data) {
            if (data['fail'] === 'csrf') {
                el2.html('CSRF Failure, try refreshing the page');
            } else if (data['fail'] === 'invalid') {
                el2.html('IMDb Lookup Failed. Please check that your imdb link is correct.');
            } else {
                el2.removeClass('has-text-centered');
                el2.html(data['content']);
                var poster;
                if (data['poster2']) {
                    poster = data['poster2'];
                } else {
                    poster = data['poster1'];
                }
                if (poster) {
                    el3.val(poster);
                    el4.html('<a href="' + poster + '" data-lightbox="images"><img src="' + poster + '" class="w-100 img-responsive" alt="Poster" /></a>');
                }
                if (data['banner']) {
                    el5.html("<img src='" + data['banner'] + "' class='w-100 round10 top20' />");
                }
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
