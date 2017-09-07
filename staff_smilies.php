<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
if ($CURUSER['class'] < UC_STAFF) {
    stderr('Error', 'Yer no tall enough');
    exit();
}
$lang = array_merge(load_language('global'));
$htmlout = '';
$htmlout = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
    <meta name='MSSmartTagsPreventParsing' content='TRUE' />
	<title>Staff Smilies</title>
    <link rel='stylesheet' href='./css/" . get_stylesheet() . "/e0a618d82ab6ae6be19a4749c87426da.min.css' />
</head>
<body>
    <script>
        function SmileIT(smile,form,text) {
            window.opener.document.forms[form].elements[text].value = window.opener.document.forms[form].elements[text].value+' '+smile+' ';
            window.opener.document.forms[form].elements[text].focus();
            window.close();
        }
    </script>
    <table class='list' width='100%' cellpadding='1' cellspacing='1'>";
$count = 0;
$ctr = 0;
global $staff_smilies;
while ((list($code, $url) = each($staff_smilies))) {
    if ($count % 3 == 0) {
        $htmlout .= '
        <tr>';
    }
    $htmlout .= "
            <td align='center'>
                <a href=\"javascript: SmileIT('" . str_replace("'", "\'", $code) . "','" . htmlsafechars($_GET['form']) . "','" . htmlsafechars($_GET['text']) . "')\">
                    <img border='0' src='{$INSTALLER09['pic_base_url']}smilies/" . $url . "' alt='' />
                </a>
            </td>";
    ++$count;
    if ($count % 3 == 0) {
        $htmlout .= '
        </tr>';
    }
}
$htmlout .= "
        </tr>
    </table><br>
    <div class='text-center'>
        <a class='altlink' href='javascript: window.close()'><b>[ Close window ]</b></a>
    </div>
</body>
</html>";
echo $htmlout;
