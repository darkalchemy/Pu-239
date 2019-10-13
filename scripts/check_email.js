function check_email() {
    var wantemail = document.getElementById('email').value;
    var url = '../ajax/emailcheck.php?wantemail=' + encodeURI(wantemail);
    try {
        var request = new ActiveXObject('Msxml2.XMLHTTP');
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
    request.onreadystatechange = emailcheck;
    request.send(null);
}

function emailcheck() {
    if (request.readyState === 4) {
        if (request.status === 200) {
            var response = request.responseText;
            let namecheck = document.getElementById('namecheck');
            document.getElementById('emailcheck').innerHTML = response;
            document.getElementById('submit').disabled = !response.includes('success') || (namecheck && !namecheck.innerHTML.includes('success'));
        }
    }
}
