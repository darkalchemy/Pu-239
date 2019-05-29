<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
$lang = load_language('global');
global $ontainer, $CURUSER, $site_config;

$body_class = 'background-16 h-style-9 text-9 skin-2';
$htmlout = doc_head() . "
    <meta property='og:title' content='All Smiles'>
    <title>All Smilies</title>
    <link rel='stylesheet' href='" . get_file_name('vendor_css') . "'>
    <link rel='stylesheet' href='" . get_file_name('css') . "'>
    <link rel='stylesheet' href='" . get_file_name('main_css') . "'>
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
$smilies = $container->get('smilies');
foreach ($smilies as $code => $url) {
    $list1 .= "
        <span class='margin10 mw-50 is-flex'>
            <span class='bordered bg-04'>
                <a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\">
                    <img src='{$site_config['paths']['images_baseurl']}smilies/" . $url . "' alt=''>
                </a>
            </span>
        </span>";
}
$customsmilies = $container->get('custom_smilies');
foreach ($customsmilies as $code => $url) {
    $list2 .= "
        <span class='margin10 mw-50 is-flex'>
            <span class='bordered bg-04'>
                <a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\">
                    <img src='{$site_config['paths']['images_baseurl']}smilies/" . $url . "' alt=''>
                </a>
            </span>
        </span>";
}
if ($CURUSER['class'] >= UC_STAFF) {
    $staff_smilies = $container->get('staff_smilies');
    foreach ($staff_smilies as $code => $url) {
        $list3 .= "
        <span class='margin10 mw-50 is-flex'>
            <span class='bordered bg-04'>
                <a href=\"javascript: pops('" . str_replace("'", "\'", $code) . "')\">
                    <img src='{$site_config['paths']['images_baseurl']}smilies/" . $url . "' alt=''>
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

$htmlout .= '
    </div>';
$htmlout .= main_div($list);
$htmlout .= '
</body>
</html>';

echo $htmlout;
