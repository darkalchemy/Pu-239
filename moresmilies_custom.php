<?php

require_once __DIR__ . '/include/bittorrent.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
global $CURUSER;

$lang = load_language('global');
if ($CURUSER['smile_until'] == '0') {
    stderr('Error', 'you do not have access!');
}
$htmlout = doc_head() . "
    <title>Custom Smilies</title>
    <link rel='stylesheet' href='" . get_file_name('css') . "'>
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
$ctr = 0;
global $customsmilies;

foreach ($customsmilies as $code => $url) {
    if ($count % 3 == 0) {
        $htmlout .= '
        <tr>';
    }
    $htmlout .= "
            <td class='has-text-centered'>
                <a href=\"javascript: SmileIT('" . str_replace("'", "\'", $code) . "','" . htmlsafechars($_GET['form']) . "','" . htmlsafechars($_GET['text']) . "')\">
                    <img src='{$site_config['pic_baseurl']}smilies/" . $url . "' alt=''>
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
