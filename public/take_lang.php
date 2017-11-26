<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache;

$lang = load_language('global');
$HTMLOUT = $out = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lid = isset($_POST['language']) ? (int)$_POST['language'] : 1;
    if ($lid > 0 && $lid != $CURUSER['id']) {
        sql_query('UPDATE users SET language = ' . sqlesc($lid) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    }
    $cache->update_row('MyUser_' . $CURUSER['id'], [
        'language' => $lid,
    ], $site_config['expires']['curuser']);
    $cache->update_row('user' . $CURUSER['id'], [
        'language' => $lid,
    ], $site_config['expires']['user_cache']);
    $HTMLOUT .= "<script>
        opener.location.reload(true);
        self.close();
      </script>";
}
$body_class = 'background-16 h-style-9 text-9 skin-2';
$HTMLOUT .= "<!doctype html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <title>Choose Language</title>
    <link rel='stylesheet' href='" . get_file('css') . "' />
</head>
<body class='$body_class'>
    <script>
        var theme = localStorage.getItem('theme');
        if (theme) {
            document.body.className = theme;
        }
    </script>
    <div class='has-text-centered'>
        <fieldset>
            <legend class='has-text-success'>Change language</legend>
            <form action='take_lang.php' method='post'>
                <p class='has-text-centered'>
                    <select name='language' onchange='this.form.submit();' size='1'>
                        <option value='1'" . (get_language() == '1' ? " selected='selected'" : '') . ">En</option>
                        <option value='2'" . (get_language() == '2' ? " selected='selected'" : '') . ">Dk</option>
                        <option value='3'" . (get_language() == '3' ? " selected='selected'" : '') . ">Rm</option>
                    </select>
                    <br>
                    <input type='button' value='Close' onclick='self.close()' />
                </p>
            </form>
        </fieldset>
    </div>
</body>
</html>";
echo $HTMLOUT;
exit();
