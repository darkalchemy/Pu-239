var animate_duration = 750;

function showMe(box) {
    var clicked = document.getElementById(event.target.id);
    var checked = clicked.checked;
    var checkboxes = document.querySelectorAll('input[type=checkbox]');
    toggle(checkboxes, false);

    var children = document.getElementsByClassName('children');
    var i;
    for (i = 0; i < children.length; i++) {
//        if (children[i].classList.contains('is_hidden')) {
//            $(children[i]).slideToggle(animate_duration, easing, function () {
//                $(children[i]).toggleClass('is_hidden', $(this).is(':visible'));
//            });


            children[i].classList.add('is_hidden');
//        }
    }

    if (checked) {
        clicked.checked = true;
        var el = document.getElementById(box);
        el.classList.remove('is_hidden');
//        $('#box').slideToggle(animate_duration, easing, function () {
//            $('#box').toggleClass('is_hidden', $(this).is(':visible'));
//        });

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
