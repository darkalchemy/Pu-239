<?php

/**
 * @param $ext
 *
 * @return bool
 */
function is_installed($ext)
{
    if (extension_loaded($ext)) {
        return true;
    }

    return false;
}

/**
 * @return string
 */
function extensionscheck()
{
    $php_min_vers = '7.2';

    $extensions = [
        'zip',
        'xml',
        'simplexml',
        'json',
        'mysqli',
        'curl',
        'exif',
        'mbstring',
        'gd',
        'bz2',
        'imagick',
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
            <div class="info" style="text-align:center;">
                It looks like you need to install some php extensions:<br>
                <span style="color: red;">' . implode(', ', $missing) . '</span>.<br>
                Once you have installed the extensions marked in red, you can continue.<br><br>
                <input type="button" value="Reload" onclick="window.location.reload()">
            </div>';
    }
    if (!$php) {
        $out .= '
            <div class="info" style="text-align:center;">
                Please update PHP to at least version ' . $php_min_vers . '.<br><br>
                <input type="button" value="Reload" onclick="window.location.reload()">
            </div>';
    }
    $out .= '
        </fieldset>';

    if (empty($missing) && $php) {
        $out .= '
            <div style="text-align:center;">
                <input type="button" onclick="onClick(1)" value="Next step">
            </div>';
    }

    $out .= '
    <script>
        var processing = 0;
    </script>';

    return $out;
}
