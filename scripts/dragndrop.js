function makeDroppable(element, callback) {

    var myinput = document.createElement('input');
    myinput.setAttribute('type', 'file');
    myinput.setAttribute('multiple', 'true');
    myinput.classList.add('is-hidden');

    myinput.addEventListener('change', triggerCallback);
    element.appendChild(myinput);

    element.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
        element.classList.add('dragover');
    });

    element.addEventListener('dragleave', function (e) {
        e.preventDefault();
        e.stopPropagation();
        element.classList.remove('dragover');
    });

    element.addEventListener('drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        element.classList.remove('dragover');
        triggerCallback(e);
    });

    element.addEventListener('click', function () {
        myinput.value = null;
        myinput.click();
    });

    function triggerCallback(e) {
        var files;
        if (e.dataTransfer) {
            files = e.dataTransfer.files;
        } else if (e.target) {
            files = e.target.files;
        }
        callback.call(null, files);
    }
}

function callback(files) {
    var output = document.querySelector('.output');
    var poster = document.querySelector('#poster');
    var droppable = document.querySelector('#droppable');
    var comment = document.querySelector('#comment');
    var loader = document.querySelector('#loader');

    comment.classList.add('is-hidden');
    loader.classList.remove('is-hidden');
    var formData = new FormData();
    for (var i = 0; i < files.length; i++) {
        formData.append('file_' + i, files[i]);
    }
    formData.append('nbr_files', i);

    $.ajax({
        url: '/ajax/take_upload.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        cache: false,
        dataType: 'json',
        success: function (response) {
            if (!poster) {
                output.innerHTML = '' +
                    '<div class="padding20">' +
                    '<h2>' + response.msg + '</h2>' +
                    '</div>';
            }
            for (i = 0; i < response.urls.length; i++) {
                if (poster) {
                    poster.value = response.urls[i];
                    droppable.classList.add('is-hidden');
                    output.innerHTML = '' +
                        '<div class="padding20 margin20 round10 bg-00">' +
                        '<img src="' + response.urls[i] + '" class="w-50 img-responsive" alt="">' +
                        '</div>';
                } else {
                    output.innerHTML += '' +
                        '<div class="padding20">' +
                        '<div class="padding20 margin10 round10 bg-00">' +
                        '<a href="' + response.urls[i] + '" data-lightbox="bitbucket">' +
                        '<img src="' + response.urls[i] + '" class="w-50 img-responsive" alt="">' +
                        '</a>' +
                        '<h2 class="has-text-centered padding20">You can use width and/or height as shown in the second link. You can use auto for one or the other.</h2>' +
                        '<h3>Direct link to image</h3>' +
                        '<input class="w-75" id="direct_' + i + '" onclick="SelectAll(\'direct_' + i + '\')" type="text" value="' + response.urls[i] + '" readonly>' +
                        '<h3 class="top20">Tag for forums or comments with Width and Height</h3>' +
                        '<input class="w-75" id="comments_' + i + '" onclick="SelectAll(\'comments_' + i + '\')" type="text" value="[img width=250 height=auto]' + response.urls[i] + '[/img]" readonly>' +
                        '</div>' +
                        '</div>';
                }
            }
        }
    });
}

var element = document.querySelector('.droppable');
makeDroppable(element, callback);

$(document).ajaxStop(function () {
    var wrapper = document.querySelector('.output-wrapper');
    var comment = document.querySelector('#comment');
    var loader = document.querySelector('#loader');
    wrapper.classList.remove('is-hidden');
    wrapper.classList.add('top20');
    comment.classList.remove('is-hidden');
    loader.classList.add('is-hidden');
});
