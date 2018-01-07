<?php

function db_test()
{
    global $root;

    require_once $root . 'include/define.php';
    require_once INCL_DIR . 'config.php';
    require_once VENDOR_DIR . 'autoload.php';

    $dotenv = new Dotenv\Dotenv(ROOT_DIR);
    $dotenv->load();

    $out = '
            <form action="index.php" method="post">
                <fieldset>
                    <legend>Database</legend>';

    $mysqli_test = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);
    if (!$mysqli_test->connect_error) {
        $out .= '<div class="readable">Connection to database was made</div>';
        if ($mysqli_test->select_db($_ENV['DB_DATABASE'])) {
            $out .= '
                    <div class="readable">Database exists, schema and data can be imported</div>
                    <div style="text-align:center;">
                        <input type="hidden" name="do" value="db_insert" />
                        <input type="hidden" name="xbt" value="' . $_GET['xbt'] . '" />
                    </div>
                 </fieldset>
                <div style="text-align:center">
                    <input type="submit" value="Import database" />
                </div>
            </form>';
        } else {
            $out .= '
                    <div class="notreadable">There was an error while selecting the database<br>' . $mysqli_test->error . '</div>
                </fieldset>
                <div style="text-align:center">
                    <input type="button" value="Reload" onclick="window.location.reload()" />
                </div>';
        }
    } else {
        $out .= '
                    <div class="notreadable">There was an error while connection to the database<br>' . $mysqli_test->connect_error . '</div>
                </fieldset>
                <div class="info" style="text-align:center">
                    <input type="button" value="Reload" onclick="window.location.reload()" />
                </div>';
    }
    $out .= '
    <script>
        var processing = 5;
    </script>';

    echo $out;
}

function db_insert()
{
    global $root, $public;

    require_once $root . 'include/define.php';
    require_once INCL_DIR . 'config.php';
    require_once VENDOR_DIR . 'autoload.php';

    $dotenv = new Dotenv\Dotenv(ROOT_DIR);
    $dotenv->load();

    $out = '<fieldset><legend>Database</legend>';

    $files = [
        'schema.php.sql',
        'data.php.sql'
    ];
    foreach ($files as $file) {
        $q = sprintf('/usr/bin/mysql -h %s -u %s -p%s %s < %sinstall/extra/' . $file, $_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], $public);
        set_time_limit(1200);
        ini_set('max_execution_time', 1200);
        ini_set('request_terminate_timeout', 1200);
        ignore_user_abort(true);
        exec($q, $o);
    }

    // update cleanup log times, begin at the previous midnight
    $timestamp = strtotime('today midnight');
    $sql = "UPDATE cleanup SET clean_time = $timestamp";
    $q = sprintf('/usr/bin/mysql -h %s -u %s -p%s %s -e "%s"', $_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], $sql);
    exec($q, $oo);

    if (!count($o) && !count($oo)) {
        $out .= '<div class="readable">Database was imported</div>
                </fieldset>
                <div style="text-align:center">
                    <input type="button" value="Finish" onclick="onClick(6)" />
                </div>';
    } else {
        $out .= '<div class="notreadable">There was an error while importing the database<br>' . print_r($o) . '</div></fieldset>';
    }
    $out .= '
    <script>
        var processing = 5;
    </script>';

    echo $out;
}
