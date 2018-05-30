<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
global $CURUSER;

$lang = load_language('global');
if ($CURUSER['smile_until'] == '0') {
    stderr('Error', 'you do not have access!');
}
$htmlout = '';
$htmlout = "<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Custom Smilies</title>
    <link rel='stylesheet' href='" . get_file_name('css') . "' />
</head>
<body>
    <script>
        function SmileIT(smile,form,text){
            window.opener.document.forms[form].elements[text].value = window.opener.document.forms[form].elements[text].value+' '+smile+' ';
            window.opener.document.forms[form].elements[text].focus();
            window.close();
        }
    </script>
    <table class='list' width='100%'>";
$count = 0;
$ctr   = 0;
global $customsmilies;
while ((list($code, $url) = each($customsmilies))) {
    if ($count % 3 == 0) {
        $htmlout .= '
        <tr>';
    }
    $htmlout .= "
            <td class='has-text-centered'>
                <a href=\"javascript: SmileIT('" . str_replace("'", "\'", $code) . "','" . htmlsafechars($_GET['form']) . "','" . htmlsafechars($_GET['text']) . "')\">
                    <img src='{$site_config['pic_baseurl']}smilies/" . $url . "' alt='' />
                </a>
            </td>";
    ++$count;
    if ($count % 3 == 0) {
        $htmlout .= '
        </tr>';
    }
}
$htmlout .= "
    </table><br>
    <div class='has-text-centered'>
        <a class='altlink' href='javascript: window.close()'><b>[ Close window ]</b></a>
    </div>
</body>
</html>";
echo $htmlout;
