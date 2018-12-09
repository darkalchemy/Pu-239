function showMe(event) {
    var clicked = document.getElementById(event.target.id);
    var parent = clicked.dataset.parent;
    var el = document.getElementById(parent);
    var checked = clicked.checked;
    var checkboxes = document.querySelectorAll('input[type=checkbox]');
    toggle(checkboxes, false);

    var children = document.getElementsByClassName('children');
    var i;
    for (i = 0; i < children.length; i++) {
        children[i].classList.add('is_hidden');
    }

    if (checked) {
        clicked.checked = true;
        el.classList.remove('is_hidden');
        checkboxes = el.querySelectorAll('input[type=checkbox]');
        toggle(checkboxes, true);
    }
}

function toggle(el, toggle)
{
    for(var i = 0, n = el.length; i < n; i++) {
        el[i].checked = toggle;
    }
}
