<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache;

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
    $time = TIME_NOW;
    $subject = '10% Addition';
    $msg = 'Today, ' . get_date($time, 'LONG', 0, 1) . ', you have increased your total upload amount by 10% from [b]' . mksize($uploaded) . '[/b] to [b]' . mksize($newuploaded) . '[/b], which brings your ratio to [b]' . $newratio . '[/b].';
    $res = sql_query("UPDATE users SET uploaded = uploaded * 1.1, tenpercent = 'yes' WHERE id = " . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $update['uploaded'] = ($CURUSER['uploaded'] * 1.1);
    $cache->update_row('user' . $CURUSER['id'], [
        'tenpercent' => 'yes',
        'uploaded' => $update['uploaded'],
    ], $site_config['expires']['user_cache']);
    $res1 = sql_query('INSERT INTO messages (sender, poster, receiver, subject, msg, added) VALUES (0, 0, ' . sqlesc($CURUSER['id']) . ', ' . sqlesc($subject) . ', ' . sqlesc($msg) . ", '" . TIME_NOW . "')") or sqlerr(__FILE__, __LINE__);
    $cache->increment('inbox_' . $CURUSER['id']);
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
$HTMLOUT .= "<h1>10&#37;</h1>
<table class='table table-bordered table-striped'>
<tr>
<td>
<p><b>How it works:</b></p>
<p class='sub'>From this page you can <b>add 10&#37;</b> of your current upload amount to your upload amount bringing it it to <b>110%</b> of its current amount. More details about how this would work out for you can be found in the tables below.</p>
<br><p><b>However, there are some things you should know first:</b></p><b>
&#8226;&#160;This can only be done <b>once</b>, so chose your moment wisely.<br>
&#8226;&#160;The staff will <b>not</b> reset your 10&#37; addition for any reason.<br><br>
</b></td></tr></table>
<table class='table table-bordered table-striped'>
<tr><td class='normalheading'>Current&#160;upload&#160;amount:</td><td class='normal'>" . str_replace(' ', '&#160;', mksize($uploaded)) . "</td><td class='embedded' width='5%'></td><td class='normalheading'>Increase:</td><td class='normal'>" . str_replace(' ', '&#160;', mksize($newuploaded - $uploaded)) . "</td><td class='embedded' width='5%'></td><td class='normalheading'>New&#160;upload&#160;amount:</td><td class='normal'>" . str_replace(' ', '&#160;', mksize($newuploaded)) . "</td></tr>
<tr><td class='normalheading'>Current&#160;download&#160;amount:</td><td class='normal'>" . str_replace(' ', '&#160;', mksize($downloaded)) . "</td><td class='embedded' width='5%'></td><td class='normalheading'>Increase:</td><td class='normal'>" . str_replace(' ', '&#160;', mksize(0)) . "</td><td class='embedded' width='5%'></td><td class='normalheading'>New&#160;download&#160;amount:</td><td class='normal'>" . str_replace(' ', '&#160;', mksize($downloaded)) . "</td></tr>
<tr><td class='normalheading'>Current&#160;ratio:</td><td class='normal'>$ratio</td><td class='embedded' width='5%'></td><td class='normalheading'>Increase:</td><td class='normal'>$ratiochange</td><td class='embedded' width='5%'></td><td class='normalheading'>New&#160;ratio:</td><td class='normal'>$newratio</td></tr>
</table>
<form name='tenpercent' method='post' action='tenpercent.php'>
<table class='table table-bordered table-striped'>
<tr><td><b>Yes please </b><input type='checkbox' name='sure' value='1' onclick='if (this.checked) enablesubmit(); else disablesubmit();' /></td></tr>
<tr><td><input type='submit' name='submit' value='Add 10%' class='button is-small' disabled /></td></tr>
</table></form>\n";
echo stdhead('Ten Percent') . $HTMLOUT . stdfoot();
