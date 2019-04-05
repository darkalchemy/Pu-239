function show_key() {
    var x = document.getElementById('select_key');
    for (var i = 0; i < x.length; i++) {
        var e = document.getElementById(x[i].value);
        e.classList.add('is_hidden');
    }
    var t = document.getElementById(x.value);
    t.classList.remove('is_hidden');
};
