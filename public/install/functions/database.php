<?php

function db_test()
{
    global $root, $site_config;
    $out = '<fieldset><legend>Database</legend>';
    require_once $root . 'include/config.php';
    $mysqli_test = new mysqli($site_config['mysql_host'], $site_config['mysql_user'], $site_config['mysql_pass'], $site_config['mysql_db']);
    if (!$mysqli_test->connect_error) {
        $out .= '<div class="readable">Connection to database was made</div>';
        if ($mysqli_test->select_db($site_config['mysql_db'])) {
            $out .= '<div class="readable">Data base exists, data can be imported</div>
                         <form action="index.php" method="post">
                             <div style="text-align:center;">
                                 <input type="hidden" name="do" value="db_insert" />
                                 <input type="hidden" name="xbt" value="' . $_GET['xbt'] . '" />
                                 <input type="submit" value="Import database" />
                             </div>
                         </form>';
        } else {
            $out .= '<div class="notreadable">There was an error while selecting the database<br>' . $mysqli_test->error . '</div>
                    </fieldset>
                    <div style="text-align:center"><input type="button" value="Reload" onclick="window.location.reload()" /></div>';
        }
    } else {
        $out .= '<div class="notreadable">There was an error while connection to the database<br>' . $mysqli_test->connect_error . '</div>
                </fieldset>
                <div class="info" style="text-align:center">
                <input type="button" value="Reload" onclick="window.location.reload()" /></div>';
    }
    echo $out;
}

//== Win - remember to set your path up correctly in the query- atm its set for appserv c:\AppServ\MySQL\bin\mysql
function db_insert()
{
    global $root, $public, $site_config;
    $out = '<fieldset><legend>Database</legend>';
    require_once $root . 'include/config.php';
    $file = 'install.php.sql';
    if ($_POST['xbt'] == 1) {
        $file = 'install.xbt.sql';
    }
    $q = sprintf('/usr/bin/mysql -h %s -u %s -p%s %s < %sinstall/extra/' . $file, $site_config['mysql_host'], $site_config['mysql_user'], $site_config['mysql_pass'], $site_config['mysql_db'], $public); //== Linux
    set_time_limit(1200);
    ini_set('max_execution_time', 1200);
    ini_set('request_terminate_timeout', 1200);
    ignore_user_abort(true);
    exec($q, $o);

    // update cleanup log times, begin at the previous midnight
    $timestamp = strtotime('today midnight');
    $sql = "UPDATE cleanup SET clean_time = $timestamp";
    $q = sprintf('/usr/bin/mysql -h %s -u %s -p%s %s -e "%s"', $site_config['mysql_host'], $site_config['mysql_user'], $site_config['mysql_pass'], $site_config['mysql_db'], $sql);
    exec($q, $oo);

    if (!count($o) && !count($oo)) {
        $out .= '<div class="readable">Database was imported</div>
                </fieldset>
                <div style="text-align:center"><input type="button" value="Finish" onclick="window.location.href=\'?step=4\'" /></div>';
        file_put_contents('step3.lock', 1);
    } else {
        $out .= '<div class="notreadable">There was an error while importing the database<br>' . $o . '</div></fieldset>';
    }
    echo $out;
}
