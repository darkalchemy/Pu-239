$('input[name=genre]').click(function () {
    var ids = ['movie', 'tv', 'music', 'game', 'apps', 'none', 'keep'];
    var value = this.value;
    ids.forEach(function (item) {
        document.getElementById(item).classList.add('is_hidden');
    });
    document.getElementById(value).classList.remove('is_hidden');
});
