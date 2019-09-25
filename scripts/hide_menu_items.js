let toggle_buttons = function (elem) {
    let divs = document.getElementsByClassName(elem);
    for (let div of divs) {
        div.classList.toggle('hidden');
    }
};
