$('#url').change(function () {
    var el1 = $('.imdb_outer');
    var el2 = $('.imdb_inner');
    var el3 = $('#poster');
    var el4 = $('.poster_container');
    var el5 = $('.banner_container');
    el1.addClass('bordered bg-00 margin10');
    el2.addClass('alt_bordered has-text-centered');
    el4.addClass('padding20 margin10 round10 bg-00');
    el2.html('Looking up IMDb and downloading/optimizing images, please be patient.');

    $.ajax({
        url: './ajax/imdb_lookup.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            csrf: this.dataset.csrf,
            url: this.value,
        },
        success: function (data) {
            if (data['fail'] === 'csrf') {
                el2.html('CSRF Failure, try refreshing the page');
            } else if (data['fail'] === 'invalid') {
                el2.html('IMDb Lookup Failed. Please check that your imdb link is correct.');
            } else {
                el2.removeClass('has-text-centered');
                el2.html(data['content']);
                if (data['poster2']) {
                    var poster = data['poster2'];
                } else {
                    var poster = data['poster1'];
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
                    document.getElementsByTagName('body')[0].style.backgroundColor = 'black';
                    document.getElementsByTagName('body')[0].style.backgroundImage = '';
                }
            }
        }
    });
});

