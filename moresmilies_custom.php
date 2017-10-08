<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
$lang = array_merge(load_language('global'));
if ($CURUSER['smile_until'] == '0') {
    stderr('Error', 'you do not have access!');
}
$htmlout = '';
$htmlout = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
    <meta name='MSSmartTagsPreventParsing' content='TRUE' />
	<title>Custom Smilies</title>
    <link rel='stylesheet' href='" . get_file('css') . "' />
</head>
<body>
    <script>
        function SmileIT(smile,form,text){
            window.opener.document.forms[form].elements[text].value = window.opener.document.forms[form].elements[text].value+' '+smile+' ';
            window.opener.document.forms[form].elements[text].focus();
            window.close();
        }
    </script>
    <table class='list' width='100%' cellpadding='1' cellspacing='1'>";
$count = 0;
$ctr = 0;
global $customsmilies;
while ((list($code, $url) = each($customsmilies))) {
    if ($count % 3 == 0) {
        $htmlout .= '
        <tr>';
    }
    $htmlout .= "
            <td class='text-center'>
                <a href=\"javascript: SmileIT('" . str_replace("'", "\'", $code) . "','" . htmlsafechars($_GET['form']) . "','" . htmlsafechars($_GET['text']) . "')\">
                    <img border='0' src='./images/smilies/" . $url . "' alt='' />
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
    <div class='text-center'>
        <a class='altlink' href='javascript: window.close()'><b>[ Close window ]</b></a>
    </div>
</body>
</html>";
echo $htmlout;
