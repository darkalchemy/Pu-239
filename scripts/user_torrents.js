if ($('#inner_torrents').length) {
    var el = document.querySelector('#inner_torrents');
    get_torrents(el.dataset.uid, 'torrents');
}

if ($('#inner_seeding').length) {
    var el = document.querySelector('#inner_seeding');
    get_torrents(el.dataset.uid, 'seeding');
}

if ($('#inner_leeching').length) {
    var el = document.querySelector('#inner_leeching');
    get_torrents(el.dataset.uid, 'leeching');
}

if ($('#inner_snatched').length) {
    var el = document.querySelector('#inner_snatched');
    get_torrents(el.dataset.uid, 'snatched');
}

if ($('#inner_snatched_staff').length) {
    var el = document.querySelector('#inner_snatched_staff');
    get_torrents(el.dataset.uid, 'snatched_staff');
}

function get_torrents(uid, type) {
    var selector = '#inner_' + type;
    var el = document.querySelector(selector);
    var e = document.createElement('div');
    e.classList.add('has-text-centered');
    e.innerHTML = 'Looking up your torrents, please be patient.';
    el.appendChild(e);

    $.ajax({
        url: './ajax/torrents_lookup.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            uid: uid,
            type: type
        },
        success: function (data) {
            if (data['fail'] === 'csrf') {
                e.innerHTML = 'CSRF Failure, try refreshing the page';
            } else if (data['fail'] === 'invalid') {
                e.innerHTML = 'Torrents Lookup Failed.';
            } else {
                e.remove();
                var node = document.createElement('div');
                node.innerHTML = data['content'];
                el.appendChild(node);
            }
        }
    });
}
