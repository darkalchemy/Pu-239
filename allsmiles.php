<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'bbcode_functions.php';
check_user_status();

$lang = array_merge(load_language('global'));
$htmlout = '';
$htmlout = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
    <meta name='MSSmartTagsPreventParsing' content='TRUE' />
	<title>More Smilies</title>
    <link rel='stylesheet' href='./templates/" . $CURUSER['stylesheet'] . "/default.css?{$INSTALLER09['code_version']}' />
</head>
<body class='background-15'>
<script>
    function pops(smile){
        textcontent=window.opener.document.getElementById('inputField').value;
        window.opener.document.getElementById('inputField').value = textcontent + ' ' + smile;
        window.opener.document.getElementById('inputField').focus();
        window.close();
    }
</script>";

$count = 0;
$list = '';
foreach ($smilies as $code => $url) {
    //$list .= "$code => $url<br>";
    $list .= "<a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\"><img border='0' src='./pic/smilies/" . $url . "' alt='' /></a>";
}
foreach ($customsmilies as $code => $url) {
    //$list .= "$code => $url<br>";
    $list .= "<a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\"><img border='0' src='./pic/smilies/" . $url . "' alt='' /></a>";
}
if ($CURUSER['class'] >= UC_STAFF) {
    foreach ($staff_smilies as $code => $url) {
        //$list .= "$code => $url<br>";
        $list .= "<a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\"><img border='0' src='./pic/smilies/" . $url . "' alt='' /></a>";
    }
}
$htmlout .= "
    <div class='container-flex'>
        $list
    </div>
";

/*<table class='list' width='100%' cellpadding='1' cellspacing='1'>";
$count = 0;
while ((list($code, $url) = each($smilies))) {
    if ($count % 2 == 0) {
        $htmlout .= " \n<tr>";
    }
    $htmlout .= "\n\t<td class=\"list\" align=\"center\"><a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\"><img border='0' src='./pic/smilies/" . $url . "' alt='' /></a></td>";
    ++$count;
    if ($count % 2 == 0) {
        $htmlout .= "\n</tr>";
    }
}

if ($CURUSER['smile_until'] != '0') {
    global $customsmilies;
    while ((list($code, $url) = each($customsmilies))) {
        if ($count % 2 == 0) {
            $htmlout .= '<tr>';
        }
        $htmlout .= "\n\t<td class=\"list\" align=\"center\"><a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\"><img border='0' src='./pic/smilies/" . $url . "' alt='' /></a></td>";
        ++$count;
        if ($count % 2 == 0) {
            $htmlout .= '</tr>';
        }
    }
}
if ($CURUSER['class'] >= UC_STAFF) {
    global $staff_smilies;
    while ((list($code, $url) = each($staff_smilies))) {
        if ($count % 2 == 0) {
            $htmlout .= '<tr>';
        }
        $htmlout .= "\n\t<td class=\"list\" align=\"center\"><a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\"><img border='0' src='./pic/smilies/" . $url . "' alt='' /></a></td>";
        ++$count;
        if ($count % 2 == 0) {
            $htmlout .= '</tr>';
        }
    }
}
$htmlout .= "</table><div align='center'><a href='javascript: window.close()'>$count Emoticons <br>[ Close Window ]</a></div></body></html>";
*/
$htmlout .= "
</body>
</html>";

echo $htmlout;
