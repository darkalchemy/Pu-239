let min_length = 2;

document.onclick = function (event) {
    closechoices(event);
};

suggcont = document.getElementById('autocomplete');

suggcont.style.display = 'none';

function autosearch(key) {
    if (key != 13) {
        let el = document.querySelector('#search');
        let csrf = el.dataset.csrf;
        let keyword = el.value;
        let lastChar = keyword.slice(-1);
        if (lastChar != ' ' && keyword.length >= min_length) {
            $.ajax({
                url: './ajax/autocomplete.php',
                type: 'POST',
                data: {
                    keyword: keyword,
                    csrf: csrf
                },
                success: function (data) {
                    $('#autocomplete').slideDown('slow', function () {
                    });
                    $('#autocomplete_list').html(data);
                }
            }).fail(function () {
                document.getElementById('autocomplete_list').innerHTML = 'No Results';
            });
        } else if (keyword.length < min_length) {
            $('#autocomplete_list').html('');
        }
    }
}

function closechoices(event) {
    if (event.target.id != 'autocomplete') {
        $('#autocomplete').slideUp('slow', function () {
        });
    }
    let keyword = $('#search').val();
    if (event.target.id == 'search' && keyword.length >= min_length) {
        $('#autocomplete').slideDown('slow', function () {
        });
    }
}
