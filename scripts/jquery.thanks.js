$(function () {
    var el = document.querySelector('#thanks_holder');
    show_thanks(el.dataset.csrf, el.dataset.tid);
});

function show_thanks(csrf, tid) {
    var holder = $('#thanks_holder');
    holder.html('Loading ...').fadeIn('slow');
    $.post('./ajax/thanks.php', {
        action: 'list',
        ajax: 1,
        tid: tid,
        csrf: csrf,
    }, function (r) {
        if (r.status) {
            if (!r.hadTh) r.list += "<input type='button' class='button is-small' value='Say thanks' onclick=\"say_thanks('" + csrf + "', " + tid + ")\" id='thanks_button' />";
            holder.empty().html(r.list);
        }
    }, 'json');
}

function say_thanks(csrf, tid) {
    $('#thanks_button').attr('value', 'Please wait...').attr('disabled', 'disabled');
    var holder = $('#thanks_holder');
    $.post('./ajax/thanks.php', {
        action: 'add',
        ajax: 1,
        tid: tid,
        csrf: csrf,
    }, function (r) {
        if (r.status) holder.empty().html(r.list); else alert(r.err);
    }, 'json');
}
