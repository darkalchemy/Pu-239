function copy_to_clipboard(elem)
{
    var text = document.getElementById(elem);
    var el = document.createElement('textarea');
    el.value = text.value;
    el.setAttribute('readonly', '');
    el.style.position = 'absolute';
    el.style.left = '-9999px';
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    alert(text.value + "\n\nCopied to clipboard.");
}

