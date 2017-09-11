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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset='UTF-8'>
    <title>Pu-239</title>
    <link type="text/css" href="extra/installer.css" rel="stylesheet" />
</head>
<body>

<div id="wrapper">
<div id="logo"></div>
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
        require_once 'functions/permissioncheck.php';
        echo permissioncheck();
        break;

    case 1:
        checkpreviousstep();
        require_once 'functions/writeconfig.php';
        $out = '<form action="index.php" method="post">';
        foreach ($foo as $fo => $fooo) {
            $out .= createblock($fo, $fooo);
        }
        $out .= '<div style="text-align:center"><input type="submit" value="Submit data" /><input type="hidden" value="write" name="do" /></div></form>';
        echo $out;
        break;

    case 2:
        checkpreviousstep();
        require_once 'functions/database.php';
        db_test();
        break;

    case 3:
        $out = '<fieldset><legend>All done</legend><div class="info">Installation complete</div><div class="info">goto <a href="./../signup.php">Signup</a> to create your first user.</div></fieldset>';
        echo $out;
        break;
    }
}
?>
</div>
</body>
</html>
