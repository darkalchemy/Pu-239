<?php

require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $lang, $site_config, $cache, $session, $site_config;

$lang = array_merge($lang, load_language('ad_sitesettings'));
$HTMLOUT .= "
            <h1 class='has-text-centered top20'>{$lang['sitesettings_sitehead']}</h1>";

$heading = '
            <tr>
                <th class="w-10">Key</th>
                <th class="w-10">Type</th>
                <th>Value</th>
            </tr>';

$body = '';
ksort($site_config);
foreach ($site_config as $key => $value) {
    if (is_array($value)) {
        if (array_key_exists(0, $value)) {
            $value = implode(', ', $value);
            $body .= "
                <tr>
                    <td><span class='has-text-lime'>$key</span></td>
                    <td>Array</td>
                    <td>$value</td>
                </tr>";
        } else {
            foreach ($value as $item => $data) {
                if (is_array($data)) {
                    $data = implode(', ', $data);
                }
                $type = 'string';
                if (is_bool($data)) {
                    $type = 'boolean';
                    $data = $data ? 'true' : 'false';
                } elseif (is_float($data)) {
                    $type = 'float';
                } elseif (is_int($data)) {
                    $type = 'integer';
                }

                $body .= "
                <tr>
                    <td><span class='has-text-lime'>$key</span><span class='has-text-danger'>::</span><span class='has-text-yellow'>$item</span></td>
                    <td>$type</td>
                    <td>$data</td>
                </tr>";
            }
        }
    } else {
        $type = 'string';
        if (is_bool($value)) {
            $type = 'boolean';
            $value = $value ? 'true' : 'false';
        } elseif (is_float($value)) {
            $type = 'float';
        } elseif (is_int($value)) {
            $type = 'integer';
        } elseif (empty($value)) {
            $value = "<span class='has-text-danger'>empty</span>";
        }
        $body .= "
            <tr>
                <td><span class='has-text-lime'>$key</span></td>
                <td>$type</td>
                <td>$value</td>
            </tr>";
    }
}

$HTMLOUT .= main_table($body, $heading);
echo stdhead($lang['sitesettings_stdhead']) . wrapper($HTMLOUT) . stdfoot();
