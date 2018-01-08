<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $CURUSER;

$lang = load_language('global');
$body_class = 'background-16 h-style-9 text-9 skin-2';
$htmlout = "<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>All Smilies</title>
    <link rel='stylesheet' href='" . get_file_name('css') . "' />
</head>
<body class='$body_class'>
    <script>
        var theme = localStorage.getItem('theme');
        if (theme) {
            document.body.className = theme;
        }
        function pops(smile){
            var textcontent = window.opener.document.getElementById('inputField').value;
            window.opener.document.getElementById('inputField').value = textcontent + ' ' + smile;
            window.opener.document.getElementById('inputField').focus();
            window.close();
        }
    </script>";

$count = 0;
$list1 = $list2 = $list3 = '';
foreach ($smilies as $code => $url) {
    $list1 .= "
        <span class='margin10 mw-50 is-flex'>
            <span class='bordered bg-04'>
                <a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\">
                    <img src='{$site_config['pic_base_url']}smilies/" . $url . "' alt='' />
                </a>
            </span>
        </span>";
}
foreach ($customsmilies as $code => $url) {
    $list2 .= "
        <span class='margin10 mw-50 is-flex'>
            <span class='bordered bg-04'>
                <a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\">
                    <img src='{$site_config['pic_base_url']}smilies/" . $url . "' alt='' />
                </a>
            </span>
        </span>";
}
if ($CURUSER['class'] >= UC_STAFF) {
    foreach ($staff_smilies as $code => $url) {
        $list3 .= "
        <span class='margin10 mw-50 is-flex'>
            <span class='bordered bg-04'>
                <a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\">
                    <img src='{$site_config['pic_base_url']}smilies/" . $url . "' alt='' />
                </a>
            </span>
        </span>";
    }
}
$list = "
    <div class='has-text-centered'>
        <h1>Smilies</h1>
        <div class='level-center bg-04 round10 margin20'>
            $list1
        </div>";

if ($CURUSER['smile_until'] != '0') {
    $list .= "
        <h1>Custom Smilies</h1>
        <div class='level-center bg-04 round10 margin20'>
            $list2
        </div>";
}

if ($CURUSER['class'] >= UC_STAFF) {
    $list .= "
        <h1>Staff Smilies</h1>
        <div class='level-center bg-04 round10 margin20'>
            $list3
        </div>";
}

$htmlout .= "
    </div>";
$htmlout .= main_div($list);
$htmlout .= "
</body>
</html>";

echo $htmlout;
