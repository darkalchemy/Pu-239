<?php
$step = isset($_GET['step']) ? (int) $_GET['step'] : 0;
$public = $_SERVER['DOCUMENT_ROOT'];
if ($public[strlen($public) - 1] != DIRECTORY_SEPARATOR) {
    $public = $public . DIRECTORY_SEPARATOR;
}
$root = realpath($public . '..') . DIRECTORY_SEPARATOR;

if (file_exists($public . 'include/install.lock')) {
    die('This was already installed, huh ? how this happened');
}

function checkpreviousstep()
{
    $step = isset($_GET['step']) ? (int) $_GET['step'] - 1 : 0;
    if (!file_exists('step' . $step . '.lock')) {
        header('Location: index.php?step=' . $step);
    }
}

function return_bytes($val)
{
    if ($val == '') {
        return 0;
    }
    $val = strtolower(trim($val));
    $last = $val[strlen($val)-1];
    $val = rtrim($val, $last);

    switch($last) {
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
    <link type='text/css' href='extra/installer.css' rel='stylesheet' />
</head>
<body>

<div id='wrapper'>
<div id='logo'></div>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valid_do = array(
        'write' => 1,
        'db_insert' => 1,
    );
    $do = isset($_POST['do']) && isset($valid_do[$_POST['do']]) ? $_POST['do'] : false;
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
        checkpreviousstep();
        require_once 'functions/permissioncheck.php';
        echo permissioncheck();
        break;

    case 2:
        checkpreviousstep();
        require_once 'functions/writeconfig.php';
        $out = '<form action="index.php" method="post">';
        foreach ($foo as $fo => $fooo) {
            $out .= createblock($fo, $fooo);
        }
        $out .= '<div style="text-align:center"><input type="submit" value="Submit data" /><input type="hidden" value="write" name="do" /></div></form>';
        echo $out;
        break;

    case 3:
        checkpreviousstep();
        require_once 'functions/database.php';
        db_test();
        break;

    case 4:
        $out = '<fieldset><legend>All done</legend><div class="info">Installation complete</div><div class="info">goto <a href="./../signup.php">Signup</a> to create your first user.</div></fieldset>';
        echo $out;
        break;

    }
}
?>
</div>
</body>
</html>
