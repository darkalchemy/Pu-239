function grab_url(event) {
    var el = document.getElementById('image_url');
    var url = el.value;
    var csrf = el.dataset.csrf
    var output = document.querySelector('.output');
    var comment = document.querySelector('#comment');
    var loader = document.querySelector('#loader');
    var poster = document.querySelector('#poster');
    var image_url = document.querySelector('#image_url');
    comment.classList.add('is-hidden');
    loader.classList.remove('is-hidden');

    $.ajax({
        url: '/ajax/take_url_upload.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            csrf: csrf,
            url: url,
        },
        success: function (response) {
            if (response.url) {
                if (poster) {
                    poster.value = response.url;
                    droppable.classList.add('is-hidden');
                    image_url.classList.add('is-hidden');
                    poster.classList.remove('is-hidden');
                    output.innerHTML = '' +
                        '<div class="padding20 margin20 round10 bg-00">' +
                        '<img src="' + response.url + '" class="w-50 img-responsive" alt="">' +
                        '</div>';
                } else {
                    output.innerHTML = '' +
                        '<div class="padding20">' +
                        '<h2 class="top10 bottom20">' + response.msg + '</h2>' +
                        '<div class="padding20 margin10 round10 bg-00">' +
                        '<a href="' + response.url + '" data-lightbox="bitbucket">' +
                        '<img src="' + response.url + '" class="w-50 img-responsive" alt="Your Image">' +
                        '</a>' +
                        '<h2 class="has-text-centered padding20">You can use width and/or height as shown in the second link. You can use auto for one or the other.</h2>' +
                        '<h3>Direct link to image</h3>' +
                        '<input class="w-75" id="direct" onclick="SelectAll(\'direct\')" type="text" value="' + response.url + '" readonly>' +
                        '<h3 class="top20">Tag for forums or comments with Width and Height</h3>' +
                        '<input class="w-75" id="comment" onclick="SelectAll(\'comment\')" type="text" value="[img width=250 height=auto]' + response.url + '[/img]" readonly>' +
                        '</div>' +
                        '</div>';
                }
            } else {
                output.innerHTML = '' +
                    '<h2>' + response.msg + '</h2>';
            }
        }
    });
}

$(document).ajaxStop(function () {
    var wrapper = document.querySelector('.output-wrapper');
    var comment = document.querySelector('#comment');
    var loader = document.querySelector('#loader');
    wrapper.classList.remove('is-hidden');
    wrapper.classList.add('top20');
    comment.classList.remove('is-hidden');
    loader.classList.add('is-hidden');
});
