function get_trivia_question() {
    var el = document.querySelector('#trivia_display');
    var content = document.querySelector('#trivia_content');
    var button = document.querySelector('#button');
    button.disabled = true;

    var qid = el.dataset.qid;
    var gamenum = el.dataset.gamenum;
    content.innerHTML = '<div class="padding20">Looking up Trivia Questions, please be patient.</div>';

    $.ajax({
        url: './ajax/trivia_lookup.php',
        type: 'POST',
        dataType: 'json',
        data: {
            qid: qid,
            gamenum: gamenum
        },
        success: function (data) {
            if (data['fail'] === 'invalid') {
                content.innerHTML = '<div class="padding20">Trivia Lookup Failed.</div>';
            } else {
                content.innerHTML = data['content'];
                initializeClock('clock_round', data['round']);
                initializeClock('clock_game', data['game']);
            }
        }
    });
}

function getTimeRemaining(endtime) {
    var t = String(Date.parse(endtime) - Date.parse(String(new Date())));
    var seconds = Math.floor((t / 1000) % 60);
    var minutes = Math.floor((t / 1000 / 60) % 60);
    var hours = Math.floor((t / (1000 * 60 * 60)) % 24);
    var days = Math.floor(t / (1000 * 60 * 60 * 24));
    return {
        'total': t,
        'days': days,
        'hours': hours,
        'minutes': minutes,
        'seconds': seconds
    };
}

function initializeClock(id, remaining) {
    var clock = document.getElementById(id);
    var ending = new Date();
    ending = new Date(ending.getTime() + 1000 * remaining);

    function updateClock() {
        var t = getTimeRemaining(ending);
        var daysSpan = clock.querySelector('.days');
        var hoursSpan = clock.querySelector('.hours');
        var minutesSpan = clock.querySelector('.minutes');
        var secondsSpan = clock.querySelector('.seconds');
        if (t.days > 0) {
            daysSpan.innerHTML = t.days + ' Days, ';
        }
        if (t.hours > 0) {
            hoursSpan.innerHTML = String(t.hours);
        }
        if (id === 'clock_game') {
            minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
        } else {
            minutesSpan.innerHTML = String(t.minutes);
        }
        secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);
        if (t.total <= 0) {
            clearInterval(timeinterval);
            var button = document.querySelector('#button');
            button.disabled = false;
        }
    }

    updateClock();
    var timeinterval = setInterval(updateClock, 1000);
}

function process_trivia(elem) {
    var el = document.querySelector('#' + elem);
    var content = document.querySelector('#trivia_content');

    var qid = el.dataset.qid;
    var gamenum = el.dataset.gamenum;
    var answer = el.dataset.answer;
    content.innerHTML = '<span class="has-text-centered">Checking your answer, please be patient.</span>';

    $.ajax({
        url: './ajax/trivia_answers.php',
        type: 'POST',
        dataType: 'json',
        context: this,
        data: {
            answer: answer,
            qid: qid,
            gamenum: gamenum
        },
        success: function (data) {
            if (data['fail'] === 'invalid') {
                content.innerHTML = '<span class="has-text-centered">Trivia Lookup Failed.</span>';
            } else {
                content.innerHTML = data['content'];
                initializeClock('clock_round', data['round']);
                initializeClock('clock_game', data['game']);
            }
        }
    });
}
