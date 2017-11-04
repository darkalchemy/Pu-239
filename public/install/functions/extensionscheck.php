<?php
function is_installed($ext)
{
    if (extension_loaded($ext)) {
        return true;
    }
    return false;
}
function extensionscheck()
{
    global $root, $public;
    $php_min_vers = '7.0';

    if (file_exists('step0.lock')) {
        header('Location: index.php?step=1');
    }
    $extensions = [
        'memcache',
        'zip',
        'xml',
        'simplexml',
        'json',
        'mysqli',
        'redis',
        'curl',
        'exif',
        'mbstring',
    ];


    $missing = [];
    $php = true;
    $out = '
        <fieldset>
            <legend>PHP Extensions Check</legend>';

    if (version_compare(phpversion(), $php_min_vers, '<')) {
        $php = false;
        $out .= '
            <div class="notreadable">PHP ' . $php_min_vers . '+ is required.</div>';
    } else {
        $out .= '
            <div class="readable">' . phpversion() . '</div>';
    }
    foreach ($extensions as $ext) {
        if (!is_installed($ext)) {
            $missing[] = $ext;
            $out .= '
            <div class="notreadable">' . $ext . '</div>';
        } else {
            $out .= '
            <div class="readable">' . $ext . '</div>';
        }
    }
    if (!empty($missing)) {
        $out .= '
            <div class="info" style="text-align:center">
                It looks like you need to install some php extensions:<br>
                <span style="color: red;">' . implode(', ', $missing) . '</span>.<br>
                Once you have installed the extensions marked in red, you can continue.<br>
                <input type="button" value="Reload" onclick="window.location.reload()" />
            </div>';
    }
    if (!$php) {
        $out .= '
            <div class="info" style="text-align:center">
                Please update PHP to at least version ' . $php_min_vers . '.<br>
                <input type="button" value="Reload" onclick="window.location.reload()" />
            </div>';
    }
    $out .= '
        </fieldset>';

    if (empty($missing) && $php) {
        $out .= '
            <div style="text-align:center">
                <input type="button" onclick="window.location.href=\'index.php?step=1\'" value="Next step" />
            </div>';
        file_put_contents('step0.lock', '1');
    }

    return $out;
}
