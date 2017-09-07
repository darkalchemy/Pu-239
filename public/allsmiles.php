<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'bbcode_functions.php';
check_user_status();

$lang = array_merge(load_language('global'));
$body_class = 'background-15 h-style-1 text-1 skin-2';
$htmlout = '';
$htmlout = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
    <meta name='MSSmartTagsPreventParsing' content='TRUE' />
    <title>More Smilies</title>
    <link rel='stylesheet' href='./css/" . get_stylesheet() . "/e0a618d82ab6ae6be19a4749c87426da.min.css' />
</head>
<body class='$body_class'>
    <script>
        var theme = localStorage.getItem('theme');
        if (theme) {
            document.body.className = theme;
        }
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
    $list .= "
        <a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\">
            <img border='0' src='./images/smilies/" . $url . "' alt='' />
        </a>";
}
foreach ($customsmilies as $code => $url) {
    $list .= "
        <a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\">
            <img border='0' src='./images/smilies/" . $url . "' alt='' />
        </a>";
}
if ($CURUSER['class'] >= UC_STAFF) {
    foreach ($staff_smilies as $code => $url) {
        $list .= "
        <a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\">
            <img border='0' src='./images/smilies/" . $url . "' alt='' />
        </a>";
    }
}
$htmlout .= "
    <div class='container-flex'>
        $list
    </div>
</body>
</html>";

echo $htmlout;
