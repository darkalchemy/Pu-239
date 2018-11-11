var min_length = 2;

suggcont = document.getElementById('autocomplete');

if (suggcont) {
    suggcont.style.display = 'none';
}

function usersearch(key) {
    if (key != 13) {
        var el = document.querySelector('#user_search');
        var csrf = el.dataset.csrf;
        var keyword = el.value;
        var lastChar = keyword.slice(-1);
        if (lastChar != ' ' && keyword.length >= min_length) {
            $.ajax({
                url: './ajax/usersearch.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    keyword: keyword,
                    csrf: csrf
                },
                success: function (data) {
                    var output = '<ul class="has-text-left">';
                    data.forEach(function (user) {
                        output += '<li class="margin10">'
                            + '<span class="size_6 ' + user['classname'] + '" onclick="selectUser(\'' + user['username'] + '\', ' + user['id'] + ')">' + user['username'] + '</span>'
                            + '</li>';
                    });
                    output += '<ul>';
                    $('#autocomplete').slideDown('slow', function () {
                    });
                    $('#autocomplete_list').html(output);
                }
            }).fail(function () {
                document.getElementById('autocomplete_list').innerHTML = 'No Results';
            });
        } else if (keyword.length < min_length) {
            document.getElementById('autocomplete_list').innerHTML = '';
            suggcont.style.display = 'none';
        }
    }
}

function selectUser(username, id) {
    document.getElementById('user_search').value = username;
    document.getElementById('receiver').value = id;
    document.getElementById('autocomplete_list').innerHTML = '';
    document.getElementById('button').disabled = false;
    suggcont.style.display = 'none';
}
