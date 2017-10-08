var flag = true;

function trivia_refresh() {
    var keyword = 'local';
    $.ajax({ url: 'ajax/trivia.php',
        data: {keyword:keyword},
        type: 'POST',
        dataType : 'json',
        success: function(data) {
            $('.showing').attr('class', 'hiding');
            if (data['gameon'] == 1) {
                initializeClock('clock_round', data['round_remaining']);
                if (flag) {
                    initializeClock('clock_game', data['game_remaining']);
                    flag = false;
                }
            }
            if (data['gameon'] == 0) {
                $('#closed').attr('class', 'showing');
            } else if (data['correct'] == 1) {
                $('#correct_notice').attr('class', 'showing');
                $('#clocks').attr('class', 'showing');
            } else if (data['correct'] == 0) {
                $('#incorrect_notice').attr('class', 'showing');
                $('#clocks').attr('class', 'showing');
            } else {
                $('#qid').val(data['qid']);
                $('#gamenum').val(data['gamenum']);
                $('#question').attr('class', 'showing');
                $('#question').html(data['question']);
                $('#answer1_parent').attr('class', 'showing');
                $('#answer1').html(data['answer1']);
                $('#answer2_parent').attr('class', 'showing');
                $('#answer2').html(data['answer2']);
                if (data['answer3']) {
                    $('#answer3_parent').attr('class', 'showing');
                    $('#answer3').html(data['answer3']);
                }
                if (data['answer4']) {
                    $('#answer4_parent').attr('class', 'showing');
                    $('#answer4').html(data['answer4']);
                }
                if (data['answer5']) {
                    $('#answer5_parent').attr('class', 'showing');
                    $('#answer5').html(data['answer5']);
                }
                $('#clocks').attr('class', 'showing');
            }
        }
    });
}

$('.trivia_submit').click(function() {
    $('.showing').attr('class', 'hiding');
    $('#checking_answer').attr('class', 'showing');
    $('#clocks').attr('class', 'showing');
    var answer = this.id;
    var qid = $('#qid').val();
    var user_id = $('#user_id').val();
    var gamenum = $('#gamenum').val();
    var token = $('#token').val();
    $.ajax({ url: 'ajax/trivia.php',
        data: {answer:answer, qid:qid, user_id:user_id, gamenum:gamenum, token:token},
        type: 'POST',
        dataType : 'json',
        success: function(data) {
            $('.showing').attr('class', 'hiding');
            if (data['correct'] == 1) {
                $('#correct').attr('class', 'showing');
            } else {
                $('#incorrect').attr('class', 'showing');
            }
            $('#clocks').attr('class', 'showing');
        }
    });
});
