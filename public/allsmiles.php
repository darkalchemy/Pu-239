<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'bbcode_functions.php';
check_user_status();

$lang = array_merge(load_language('global'));
$body_class = 'background-16 h-style-9 text-9 skin-2';
$htmlout = "<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>All Smilies</title>
    <link rel='stylesheet' href='" . get_file('css') . "' />
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
        <span class='margin10 mw-50 is-flex'>
            <span class='bordered'>
                <a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\">
                    <img src='./images/smilies/" . $url . "' alt='' />
                </a>
            </span>
        </span>";
}
foreach ($customsmilies as $code => $url) {
    $list .= "
        <span class='margin10 mw-50 is-flex'>
            <span class='bordered'>
                <a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\">
                    <img src='./images/smilies/" . $url . "' alt='' />
                </a>
            </span>
        </span>";
}
if ($CURUSER['class'] >= UC_STAFF) {
    foreach ($staff_smilies as $code => $url) {
        $list .= "
        <span class='margin10 mw-50 is-flex'>
            <span class='bordered'>
                <a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\">
                    <img src='./images/smilies/" . $url . "' alt='' />
                </a>
            </span>
        </span>";
    }
}
$htmlout .= "
    <div class='level-center'>
        $list
    </div>
</body>
</html>";

echo $htmlout;
