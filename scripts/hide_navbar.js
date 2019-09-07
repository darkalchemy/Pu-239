document.addEventListener('DOMContentLoaded', function () {
    let mnavbar = document.getElementById('navbar');
    let mhamburger = document.getElementById('hamburger');
    let prevScrollpos = window.pageYOffset;
    window.onscroll = function () {
        let currentScrollPos = window.pageYOffset;
        if (prevScrollpos > currentScrollPos) {
            mnavbar.style.top = '0';
            mhamburger.style.top = '15px';
        } else {
            mnavbar.style.top = '-50px';
            mhamburger.style.top = '-50px';
        }
        prevScrollpos = currentScrollPos;
    };
});
