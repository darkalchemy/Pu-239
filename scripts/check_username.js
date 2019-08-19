function check_name() {
    wantusername = document.getElementById('username').value;
    var url = '../ajax/namecheck.php?wantusername=' + encodeURI(wantusername);
    try {
        request = new ActiveXObject('Msxml2.XMLHTTP');
    } catch (e) {
        try {
            request = new ActiveXObject('Microsoft.XMLHTTP');
        } catch (e2) {
            request = false;
        }
    }
    if (!request && typeof XMLHttpRequest != 'undefined') {
        request = new XMLHttpRequest();
    }
    request.open('GET', url, true);
    request.onreadystatechange = namecheck;
    request.send(null);
}

function namecheck() {
    if (request.readyState === 4) {
        if (request.status === 200) {
            var response = request.responseText;
            let emailcheck = document.getElementById('emailcheck');
            document.getElementById('namecheck').innerHTML = response;
            document.getElementById('submit').disabled = !response.includes('success') || (emailcheck && !emailcheck.innerHTML.includes('success'));
        }
    }
}
