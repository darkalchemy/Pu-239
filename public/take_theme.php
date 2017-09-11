<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
$lang = array_merge(load_language('global'));
$HTMLOUT = $out = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sid = isset($_POST['stylesheet']) ? (int)$_POST['stylesheet'] : 1;
    if ($sid > 0 && $sid != $CURUSER['id']) {
        sql_query('UPDATE users SET stylesheet=' . sqlesc($sid) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    }
    $mc1->begin_transaction('MyUser_' . $CURUSER['id']);
    $mc1->update_row(false, [
        'stylesheet' => $sid,
    ]);
    $mc1->commit_transaction($site_config['expires']['curuser']);
    $mc1->begin_transaction('user' . $CURUSER['id']);
    $mc1->update_row(false, [
        'stylesheet' => $sid,
    ]);
    $mc1->commit_transaction($site_config['expires']['user_cache']);
    $HTMLOUT .= "<script>
        opener.location.reload(true);
        self.close();
      </script>";
}
$body_class = 'background-15 h-style-1 text-1 skin-2';
$HTMLOUT .= "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <title>Choose theme</title>
    <link rel='stylesheet' href='./css/" . get_stylesheet() . "/e0a618d82ab6ae6be19a4749c87426da.min.css' />
</head>
<body class='$body_class'>
    <script>
        var theme = localStorage.getItem('theme');
        if (theme) {
            document.body.className = theme;
        }
    </script>
    <div class='text-center'>
        <fieldset>
            <legend class='text-lime'>Change theme</legend>
            </script>
            <form action='take_theme.php' method='post'>
                <p class='text-center'>
                    <select name='stylesheet' onchange='this.form.submit();' size='1' style='font-family: Verdana; font-size: 8pt; color: #000000; border: 1px solid #808080; background-color: #ececec'>";
$ss_r = sql_query('SELECT id, name from stylesheets ORDER BY id ASC') or sqlerr(__FILE__, __LINE__);
while ($ar = mysqli_fetch_assoc($ss_r)) {
    $out .= '
                        <option value="' . (int)$ar['id'] . '" ' . ($ar['id'] == $CURUSER['stylesheet'] ? 'selected=\'selected\'' : '') . '>' . htmlsafechars($ar['name']) . '</option>';
}
$HTMLOUT .= $out;
//$HTMLOUT .= getTplOption();
$HTMLOUT .= "
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
