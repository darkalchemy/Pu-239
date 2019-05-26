function checkit() {
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
    global_content = username;
    request.onreadystatechange = check;
    request.send(null);
}

function check() {
    if (request.readyState == 4) {
        if (request.status == 200) {
            var response = request.responseText;
            document.getElementById('namecheck').innerHTML = response;
            if (response.includes('danger')) {
                document.getElementById('submit').disabled = true;
            } else {
                document.getElementById('submit').disabled = false;
            }
        }
    }
}
