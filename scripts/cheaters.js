var check1flag = 'false';
var check2flag = 'false';

function check1(field) {
    if (check1flag === 'false') {
        for (i = 0; i < field.length; i++) {
            field[i].checked = true;
        }
        check1flag = 'true';
        return 'Uncheck All Disable';
    } else {
        for (i = 0; i < field.length; i++) {
            field[i].checked = false;
        }
        check1flag = 'false';
        return 'Check All Disable';
    }
}

function check2(field) {
    if (check2flag === 'false') {
        for (i = 0; i < field.length; i++) {
            field[i].checked = true;
        }
        check2flag = 'true';
        return 'Uncheck All Remove';
    } else {
        for (i = 0; i < field.length; i++) {
            field[i].checked = false;
        }
        check2flag = 'false';
        return 'Check All Remove';
    }
}
