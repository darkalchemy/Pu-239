var csrf = document.querySelector('#csrf');
var movies = JSON.parse(csrf.dataset.movies);
var ebooks = JSON.parse(csrf.dataset.ebooks);
var el = document.querySelector('#upload_category');
var url = document.querySelector('#url');
var youtube =  document.querySelector('#youtube');
var isbn = document.querySelector('#isbn');
var subs = document.querySelector('#subs');

var parent_url = url.parentNode.parentNode;
var parent_youtube = youtube.parentNode.parentNode;
var parent_isbn = isbn.parentNode.parentNode;
var parent_subs = subs.parentNode.parentNode;

parent_url.style.display = 'none';
parent_youtube.style.display = 'none';
parent_isbn.style.display = 'none';
parent_subs.style.display = 'none';

el.addEventListener('change', function(e) {
    var cat = el.value;
    var movie = search_array(cat, movies);
    var ebook = search_array(cat, ebooks);

    if (movie) {
        parent_url.style.display = 'table-row';
        parent_youtube.style.display = 'table-row';
        parent_subs.style.display = 'table-row';
        url.required = true;
    } else {
        parent_url.style.display = 'none';
        parent_youtube.style.display = 'none';
        parent_subs.style.display = 'none';
        url.required = false;
    }
    if (ebook) {
        parent_isbn.style.display = 'table-row';
        isbn.required = true;
    } else {
        parent_isbn.style.display = 'none';
        isbn.required = false;
    }
});

var nfo = document.querySelector('#nfo');
var strip = document.querySelector('#strip');
var parent_strip = strip.parentNode.parentNode.parentNode;

parent_strip.style.display = 'none';

nfo.addEventListener('change', function(e) {
    parent_strip.style.display = 'table-row';
});

document.getElementById('announce_url').onclick = function() {
    this.select();
};

function search_array(cat, arr)
{
    for(var i=0; i < arr.length; i++) {
        if (arr[i] == cat) {
            return true;
        }
    }

    return false;
}
