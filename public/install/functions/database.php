<?php

function db_test()
{
    global $root;

    require_once $root . 'include' . DIRECTORY_SEPARATOR . 'define.php';
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
                    <div class="readable">Database exists</div>
                    <div class="readable">Schema can be created</div>
                    <div class="readable">Data can be imported</div>
                    <div style="text-align:center;">
                        <input type="hidden" name="do" value="db_insert" />
                        <input type="hidden" name="xbt" value="' . $_GET['xbt'] . '" />
                    </div>
                 </fieldset>
                <div style="text-align:center;">
                    <input type="submit" value="Import database" />
                </div>
            </form>';
        } else {
            $out .= '
                    <div class="notreadable">There was an error while selecting the database<br>' . $mysqli_test->error . '</div>
                </fieldset>
                <div style="text-align:center;">
                    <input type="button" value="Reload" onclick="window.location.reload()" />
                </div>';
        }
    } else {
        $out .= '
                    <div class="notreadable">There was an error while connection to the database<br>' . $mysqli_test->connect_error . '</div>
                </fieldset>
                <div class="info" style="text-align:center;">
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

    require_once $root . 'include' . DIRECTORY_SEPARATOR . 'define.php';
    require_once INCL_DIR . 'config.php';
    require_once VENDOR_DIR . 'autoload.php';

    $dotenv = new Dotenv\Dotenv(ROOT_DIR);
    $dotenv->load();

    $out = '<fieldset><legend>Database</legend>';

    $timestamp = strtotime('today midnight');
    $fail = '';
    $query = 'SHOW VARIABLES LIKE "innodb_large_prefix"';
    $sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], $query);
    $retval = shell_exec($sql);
    if (!preg_match('/innodb_large_prefix\s+ON/', $retval)) {
        $fail .= "<div class='notreadable'>Please add/update my.cnf 'innodb_large_prefix = 1' and restart mysql.</div>";
    }

    $query = 'SHOW VARIABLES LIKE "innodb_file_format"';
    $sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], $query);
    $retval = shell_exec($sql);
    if (!preg_match('/innodb_file_format\s+Barracuda/', $retval)) {
        $fail .= "<div class='notreadable'>Please add/update my.cnf 'innodb_file_format = Barracuda' and restart mysql.</div>";
    }

    $query = 'SHOW VARIABLES LIKE "innodb_file_per_table"';
    $sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], $query);
    $retval = shell_exec($sql);
    if (!preg_match('/innodb_file_per_table\s+ON/', $retval)) {
        $fail .= "<div class='notreadable'>Please add/update my.cnf 'innodb_file_per_table = 1' and restart mysql.</div>";
    }

    $sources = [
        'schema'     => "source {$public}install/extra/schema.php.sql",
        'data'       => "source {$public}install/extra/data.php.sql",
        'timestamps' => "UPDATE cleanup SET clean_time = $timestamp",
        'stats'      => 'INSERT INTO stats (regusers) VALUES (1)',
    ];

    if (empty($fail)) {
        foreach ($sources as $name => $source) {
            $sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], $source);
            set_time_limit(1200);
            ini_set('max_execution_time', 1200);
            ini_set('request_terminate_timeout', 1200);
            ignore_user_abort(true);
            exec($sql, $output, $retval);
            if ($retval != 0) {
                $fail .= "<div class='notreadable'>There was an error while creating the database $name</div>";
            }
        }
    }

    if (empty($fail)) {
        $out .= '<div class="readable">Database was imported</div>
                </fieldset>
                <div style="text-align:center;">
                    <input type="button" value="Finish" onclick="onClick(6)" />
                </div>';
    } else {
        $out .= "
                    $fail
                </fieldset>";
    }
    $out .= '
    <script>
        var processing = 5;
    </script>';

    echo $out;
}
