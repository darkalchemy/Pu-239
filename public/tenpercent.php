<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';

check_user_status();
global $CURUSER, $site_config, $cache, $message_stuffs;

$HTMLOUT = '';
$lang = load_language('global');

$uploaded = (int) $CURUSER['uploaded'];
$downloaded = (int) $CURUSER['downloaded'];
$newuploaded = (int) ($uploaded * 1.1);
if ($downloaded > 0) {
    $ratio = number_format($uploaded / $downloaded, 3);
    $newratio = number_format($newuploaded / $downloaded, 3);
    $ratiochange = number_format(($newuploaded / $downloaded) - ($uploaded / $downloaded), 3);
} elseif ($uploaded > 0) {
    $ratio = $newratio = $ratiochange = 'Inf.';
} else {
    $ratio = $newratio = $ratiochange = '---';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($CURUSER['tenpercent'] === 'yes') {
        stderr('Used', 'It appears that you have already used your 10% addition.');
    }
    $sure = (isset($_POST['sure']) ? intval($_POST['sure']) : '');
    if (!$sure) {
        stderr('Are you sure?', "It appears that you are not yet sure whether you want to add 10% to your upload or not. Once you are sure you can <a href='tenpercent.php'>return</a> to the 10% page.");
    }
    $dt = TIME_NOW;
    $subject = '10% Addition';
    $msg = 'Today, ' . get_date($dt, 'LONG', 0, 1) . ', you have increased your total upload amount by 10% from [b]' . mksize($uploaded) . '[/b] to [b]' . mksize($newuploaded) . '[/b], which brings your ratio to [b]' . $newratio . '[/b].';
    $res = sql_query("UPDATE users SET uploaded = uploaded * 1.1, tenpercent = 'yes' WHERE id = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $update['uploaded'] = ($CURUSER['uploaded'] * 1.1);
    $cache->update_row('user_' . $CURUSER['id'], [
        'tenpercent' => 'yes',
        'uploaded' => $update['uploaded'],
    ], $site_config['expires']['user_cache']);
    $msgs_buffer[] = [
        'sender' => 0,
        'receiver' => $CURUSER['id'],
        'added' => $dt,
        'msg' => $msg,
        'subject' => $subject,
    ];
    $message_stuffs->insert($msgs_buffer);
    if (!$res) {
        stderr('Error', 'It appears that something went wrong while trying to add 10% to your upload amount.');
    } else {
        stderr('10% Added', 'Your total upload amount has been increased by 10% from <b>' . mksize($uploaded) . '</b> to <b>' . mksize($newuploaded) . "</b>, which brings your ratio to <b>$newratio</b>.");
    }
}
if ($CURUSER['tenpercent'] === 'no') {
    $HTMLOUT .= '
  <script>
  /*<![CDATA[*/
  function enablesubmit() {
    document.tenpercent.submit.disabled = document.tenpercent.submit.checked;
  }
  function disablesubmit() {
    document.tenpercent.submit.disabled = !document.tenpercent.submit.checked;
  }
  /*]]>*/
  </script>';
}
if ($CURUSER['tenpercent'] === 'yes') {
    stderr('Oops', 'It appears that you have already used your 10% addition');
    die();
}
$HTMLOUT .= "<h1 class='has-text-centered'>10&#37;</h1>" . main_div("
<p><b>How it works:</b></p>
<p class='sub'>From this page you can <b>add 10&#37;</b> of your current upload amount to your upload amount bringing it it to <b>110%</b> of its current amount. More details about how this would work out for you can be found in the tables below.</p><br>
<p><b>However, there are some things you should know first:</b></p>
&#8226; This can only be done <b>once</b>, so chose your moment wisely.<br>
&#8226; The staff will <b>not</b> reset your 10&#37; addition for any reason.", null, 'padding20') . main_table('
    <tr>
        <td>Current upload amount:</td>
        <td>' . mksize($uploaded) . '</td>
        <td>Increase:</td>
        <td>' . mksize($newuploaded - $uploaded) . '</td>
        <td>New upload amount:</td>
        <td>' . mksize($newuploaded) . '</td>
    </tr>
    <tr>
        <td>Current download amount:</td>
        <td>' . mksize($downloaded) . '</td>
        <td>Increase:</td>
        <td>' . mksize(0) . '</td>
        <td>New download amount:</td><td>' . mksize($downloaded) . "</td>
    </tr>
    <tr>
        <td>Current ratio:</td>
        <td>$ratio</td>
        <td>Increase:</td>
        <td>$ratiochange</td>
        <td>New ratio:</td>
        <td>$newratio</td>
    </tr>", '', 'top20 bottom20') . main_div("
    <form name='tenpercent' method='post' action='tenpercent.php' accept-charset='utf-8'>
        <div class='has-text-centered padding10'>
            <label for='sure'><b>Yes please </b></label>
            <input type='checkbox' name='sure' value='1' onclick='if (this.checked) enablesubmit(); else disablesubmit();'>
        </div>
        <div class='has-text-centered padding10'>
            <input type='submit' name='submit' value='Add 10%' class='button is-small' disabled>
        </div>
    </form>");
echo stdhead('Ten Percent') . wrapper($HTMLOUT) . stdfoot();
