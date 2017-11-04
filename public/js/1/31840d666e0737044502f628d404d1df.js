var form = "checkme";

function SetChecked(val, chkName) {
    dml = document.forms[form];
    len = dml.elements.length;
    var i = 0;
    for (i = 0; i < len; i++) {
        if (dml.elements[i].name == chkName) {
            dml.elements[i].checked = val;
        }
    }
}