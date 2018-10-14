$(function () {
    var el = document.querySelector('#ipports');
    var uid = el.dataset.uid

    $.ajax({
        url: './ajax/checkports.php',
        data: {
            uid: uid
        },
        type: 'POST',
        dataType: 'json',
        success: function (output) {
            $('#ipports').html(output.data);
        }
    });
});

$('#portcheck').click(function () {
    var ip = $('#userip').val();
    var port = $('#userport').val();
    $('#ipport').val('Checking Status of Port ' + port);
    $.ajax({
        url: './ajax/checkport.php',
        data: {
            ip: ip,
            port: port
        },
        type: 'POST',
        dataType: 'json',
        success: function (output) {
            $('#ipport').val(output.data['text']);
            $('#ipport').addClass(output.data['class']);
        }
    });
});
