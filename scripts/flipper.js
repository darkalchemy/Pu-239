var x = document.getElementsByClassName('flipper');
var nextSibling;
var child;
var i;
for (i = 0; i < x.length; i++) {
    var id = x[i].parentNode.id;
    var el = document.getElementById(id);
    if (id && localStorage[id] === 'closed') {
        el.classList.add('no-margin');
        el.classList.add('no-padding');
        nextSibling = x[i].nextSibling, child;
        while (nextSibling && nextSibling.nodeType !== 1) {
            nextSibling = nextSibling.nextSibling;
        }
        nextSibling.style.display = 'none';
        child = x[i].children[0];
        child.classList.add('icon-up-open');
        child.classList.remove('icon-down-open');
    } else if (id && localStorage[id] === 'open') {
        nextSibling = x[i].nextSibling, child;
        while (nextSibling && nextSibling.nodeType !== 1) {
            nextSibling = nextSibling.nextSibling;
        }
        nextSibling.style.display = 'block';
        child = x[i].children[0];
        child.classList.add('icon-down-open');
        child.classList.remove('icon-up-open');
    } else {
        if (el && document.getElementById(el.children[0]) && document.getElementById(el.children[0].children[0]) && el.children[0].children[0].className === 'icon-down-open') {
            el.classList.add('no-margin');
            el.classList.add('no-padding');
            nextSibling = x[i].nextSibling;
            while (nextSibling && nextSibling.nodeType !== 1) {
                nextSibling = nextSibling.nextSibling;
            }
            nextSibling.style.display = 'none';
        }
    }
}

var flipper = $('.flipper');
flipper.hover(function () {
    $(this).children('i').addClass('rotate rotate-hover');
}, function () {
    $(this).children('i').removeClass('rotate-hover');
});

flipper.click(function (e) {
    $(this).next().slideToggle(animate_duration, easing, function () {
        var id = $(this).parent().attr('id');
        if (!$(this).is(':visible')) {
            if (typeof Storage !== 'undefined') {
                localStorage.setItem(id, 'closed');
            }
            $(this).parent().addClass('no-margin no-padding');
        } else {
            if (typeof Storage !== 'undefined') {
                localStorage.setItem(id, 'open');
            }
            $(this).parent().removeClass('no-margin no-padding');
        }
        $(this).parent().find('.rotate').toggleClass('icon-up-open icon-down-open').removeClass('rotate rotate-hover');
    });
});
