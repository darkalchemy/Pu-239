<?php

$step = isset($_GET['step']) ? (int) $_GET['step'] : 0;

$root = dirname(__FILE__, 3) . DIRECTORY_SEPARATOR;
$public = $root . 'public';

if ($public[strlen($public) - 1] != DIRECTORY_SEPARATOR) {
    $public = $public . DIRECTORY_SEPARATOR;
}

function return_bytes($val)
{
    if ($val == '') {
        return 0;
    }
    $val = strtolower(trim($val));
    $last = $val[strlen($val) - 1];
    $val = rtrim($val, $last);

    switch ($last) {
        case 'g':
            $val *= (1024 * 1024 * 1024);
            break;
        case 'm':
            $val *= (1024 * 1024);
            break;
        case 'k':
            $val *= 1024;
            break;
    }

    return $val;
}

function get_scheme()
{
    $scheme = '';
    if (isset($_SERVER['REQUEST_SCHEME'])) {
        $scheme = $_SERVER['REQUEST_SCHEME'];
    }

    return $scheme;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Pu-239</title>
    <link type='text/css' href='extra/installer.css' rel='stylesheet'/>
</head>
<body>
<div id='wrapper'>
    <div id='logo'></div>
    <?php

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $valid_do = [
            'write' => 1,
            'db_insert' => 1,
        ];
        $do = isset($_POST['do'], $valid_do[$_POST['do']]) ? $_POST['do'] : false;
        switch ($do) {
            case 'write':
                require_once 'functions/writeconfig.php';
                saveconfig();
                break;

            case 'db_insert':
                require_once 'functions/database.php';
                db_insert();
                break;

            default:
                print '<fieldset><div class="notreadable">Unknown action</div></fieldset>';
        }
    } else {
        switch ($step) {
            case 0:
                require_once 'functions/extensionscheck.php';
                echo extensionscheck();
                break;

            case 1:
                require_once 'functions/permissioncheck.php';
                echo permissioncheck();
                break;

            case 2:
                require_once 'functions/composercheck.php';
                echo composercheck();
                break;

            case 3:
                require_once 'functions/nodecheck.php';
                echo nodecheck();
                break;

            case 4:
                $foo = [];
                require_once 'functions/writeconfig.php';
                $out = '
                <form action="index.php" method="post">';
                foreach ($foo as $fo => $fooo) {
                    $out .= createblock($fo, $fooo);
                }
                $out .= '
                    <div style="text-align:center;">
                        <input type="submit" value="Submit data" />
                        <input type="hidden" value="write" name="do" />
                    </div>
                    <script>
                        var processing = 4;
                    </script>
                </form>';
                echo $out;
                break;

            case 5:
                require_once 'functions/database.php';
                db_test();
                break;

            case 6:
                global $site_config;

                $out = '
                <fieldset>
                    <legend>All done</legend>
                    <div class="info">Installation complete</div>
                    <div class="info">goto <a href="' . $site_config['baseurl'] . '/signup.php">Signup</a> to create your first user.</div>
                    <script>
                        var processing = 6;
                    </script>
                </fieldset>';
                unset($root, $public);
                echo $out;
                break;
        }
    }
    ?>
    <script>
        window.addEventListener('load', clear_storage());

        function clear_storage() {
            //localStorage.clear();
            if (localStorage.getItem('in_process') == null) {
                localStorage.setItem('in_process', 0);
                localStorage.setItem('step', 0);
            }
            var step = parseInt(localStorage.getItem('step'));
            if (processing !== step) {
                localStorage.clear();
                window.location.href = 'index.php?step=0';
            }
        }

        function onClick(step) {
            localStorage.setItem('step', step++);
            var step = parseInt(localStorage.getItem('step'));
            window.location.href = 'index.php?step=' + step + '&xbt=0';
        }
    </script>
</div>
</body>
</html>
