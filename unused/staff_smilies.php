<?php

declare(strict_types = 1);

require_once __DIR__ . '/include/bittorrent.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
$lang = load_language('global');
global $site_config, $CURUSER;

if ($CURUSER['class'] < UC_STAFF) {
    stderr('Error', 'Yer no tall enough');
    die();
}

$htmlout = doc_head('Staff Smilies') . "
    <link rel='stylesheet' href='" . get_file_name('css') . "'>
</head>
<body>
    <script>
        function SmileIT(smile,form,text) {
            window.opener.document.forms[form].elements[text].value = window.opener.document.forms[form].elements[text].value+' '+smile+' ';
            window.opener.document.forms[form].elements[text].focus();
            window.close();
        }
    </script>
    <table class='list'>";
$count = 0;
$ctr = 0;
foreach ($staff_smilies as $code => $url) {
    if ($count % 3 == 0) {
        $htmlout .= '
        <tr>';
    }
    $htmlout .= "
            <td class='has-text-centered'>
                <a href=\"javascript: SmileIT('" . str_replace("'", "\'", $code) . "','" . htmlsafechars($_GET['form']) . "','" . htmlsafechars($_GET['text']) . "')\">
                    <img src='{$site_config['paths']['images_baseurl']}smilies/" . $url . "' alt=''>
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
    <div class='has-text-centered'>
        <a class='is-link' href='javascript: window.close()'><b>[ Close window ]</b></a>
    </div>
</body>
</html>";
echo $htmlout;
