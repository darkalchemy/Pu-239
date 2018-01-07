<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache;

$lang = load_language('global');
$HTMLOUT = $out = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sid = isset($_POST['stylesheet']) ? (int)$_POST['stylesheet'] : 1;
    if ($sid > 0 && $sid != $CURUSER['id']) {
        sql_query('UPDATE users SET stylesheet=' . sqlesc($sid) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    }
    $cache->update_row('user' . $CURUSER['id'], [
        'stylesheet' => $sid,
    ], $site_config['expires']['user_cache']);
    $HTMLOUT .= "<script>
        opener.location.reload(true);
        self.close();
      </script>";
}
$body_class = 'background-16 h-style-9 text-9 skin-2';
$HTMLOUT .= "<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Choose theme</title>
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
            <legend class='has-text-success'>Change theme</legend>
            </script>
            <form action='take_theme.php' method='post'>
                <p class='has-text-centered'>
                    <select name='stylesheet' onchange='this.form.submit();' size='1'>";
$ss_r = sql_query('SELECT id, name FROM stylesheets ORDER BY id ASC') or sqlerr(__FILE__, __LINE__);
while ($ar = mysqli_fetch_assoc($ss_r)) {
    $out .= '
                        <option value="' . (int)$ar['id'] . '" ' . ($ar['id'] == $CURUSER['stylesheet'] ? 'selected=\'selected\'' : '') . '>' . htmlsafechars($ar['name']) . '</option>';
}
$HTMLOUT .= $out;
$HTMLOUT .= "
                    </select>
                    <br>
                    <input type='button' class='button is-small margin20' value='Close' onclick='self.close()' />
                </p>
            </form>
        </fieldset>
    </div>
</body>
</html>";
echo $HTMLOUT;
die();
