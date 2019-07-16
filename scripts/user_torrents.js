if ($('#inner_torrents').length) {
    let tit = document.querySelector('#inner_torrents');
    get_torrents(tit.dataset.uid, 'torrents');
}

if ($('#inner_seeding').length) {
    let tis = document.querySelector('#inner_seeding');
    get_torrents(tis.dataset.uid, 'seeding');
}

if ($('#inner_leeching').length) {
    let til = document.querySelector('#inner_leeching');
    get_torrents(til.dataset.uid, 'leeching');
}

if ($('#inner_snatched').length) {
    let tin = document.querySelector('#inner_snatched');
    get_torrents(tin.dataset.uid, 'snatched');
}

if ($('#inner_snatched_staff').length) {
    let tins = document.querySelector('#inner_snatched_staff');
    get_torrents(tins.dataset.uid, 'snatched_staff');
}

function get_torrents(uid, type) {
    let selector = '#inner_' + type;
    let selel = document.querySelector(selector);
    let div = document.createElement('div');
    div.classList.add('has-text-centered');
    div.innerHTML = 'Looking up your torrents, please be patient.';
    selel.appendChild(div);

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
                div.innerHTML = 'CSRF Failure, try refreshing the page';
            } else if (data['fail'] === 'invalid') {
                div.innerHTML = 'Torrents Lookup Failed.';
            } else {
                div.remove();
                let cnode = document.createElement('div');
                cnode.innerHTML = data['content'];
                selel.appendChild(cnode);
            }
        }
    });
}
