<?php

declare(strict_types = 1);

$genres = [
    $lang['upload_movie'],
    $lang['upload_tv'],
    $lang['upload_music'],
    $lang['upload_game'],
    $lang['upload_apps'],
    $lang['upload_none'],
];

$body = "
                    <div class='columns is-gapless'>" . (!empty($row['newgenre']) ? "
                        <div class='column'>
                            <div class='level-center-center top20 bottom20'>
                                <label for='keep_it' class='right5'>{$lang['upload_no_changes']}</label>
                                <input type='radio' name='genre' value='keep' id='keep_it' checked>
                            </div>
                        </div>" : '');

for ($x = 0; $x < count($genres); ++$x) {
    $body .= "
                        <div class='column'>
                            <div class='level-center-center top20 bottom20'>
                                <label for='{$genres[$x]}' class='right5'>{$genres[$x]}</label>
                                <input type='radio' value='" . strtolower($genres[$x]) . "' id='{$genres[$x]}' name='genre'>
                            </div>
                        </div>";
}
$body .= "
                    </div>";
$genres_text = main_div($body);
$genres = [
    'keep' => [(!empty($row['newgenre']) ? htmlsafechars($row['newgenre']) : '')],
    'movie' => [
        $lang['upload_action'],
        $lang['upload_adult'],
        $lang['upload_adventure'],
        $lang['upload_comedy'],
        $lang['upload_crime'],
        $lang['upload_documentary'],
        $lang['upload_drama'],
        $lang['upload_family'],
        $lang['upload_fantasy'],
        $lang['upload_historical'],
        $lang['upload_horror'],
        $lang['upload_mystery'],
        $lang['upload_political'],
        $lang['upload_reality'],
        $lang['upload_scifi'],
        $lang['upload_slasher'],
        $lang['upload_suspense'],
        $lang['upload_thriller'],
        $lang['upload_western'],
    ],
    'tv' => [
        $lang['upload_action'],
        $lang['upload_adult'],
        $lang['upload_adventure'],
        $lang['upload_comedy'],
        $lang['upload_crime'],
        $lang['upload_documentary'],
        $lang['upload_drama'],
        $lang['upload_family'],
        $lang['upload_fantasy'],
        $lang['upload_historical'],
        $lang['upload_horror'],
        $lang['upload_mystery'],
        $lang['upload_political'],
        $lang['upload_reality'],
        $lang['upload_scifi'],
        $lang['upload_slasher'],
        $lang['upload_suspense'],
        $lang['upload_thriller'],
        $lang['upload_western'],
    ],
    'music' => [
        $lang['upload_hiphop'],
        $lang['upload_rock'],
        $lang['upload_pop'],
        $lang['upload_house'],
        $lang['upload_techno'],
        $lang['upload_commercial'],
    ],
    'game' => [
        $lang['upload_fps'],
        $lang['upload_strategy'],
        $lang['upload_adventure'],
        $lang['upload_3rd'],
        $lang['upload_action'],
    ],
    'apps' => [
        $lang['upload_burning'],
        $lang['upload_encoding'],
        $lang['upload_virus'],
        $lang['upload_office'],
        $lang['upload_os'],
        $lang['upload_misc'],
        $lang['upload_image'],
    ],
    'none' => [
        $lang['upload_none'],
    ],
];

$body = '';
foreach ($genres as $key => $value) {
    $class = 'is_hidden';
    if (!empty($row['newgenre']) && $key === 'keep' || empty($row['newgenre']) && $key === 'none') {
        $class = '';
    }
    $body .= "
                    <div id='$key' class='$class level-center'>";
    for ($x = 0; $x < count($value); ++$x) {
        $body .= "
                        <div id='$key' class='level-center padding20'>
                            <label for='{$value[$x]}' class='right10'>{$value[$x]}</label>" . ($key === 'keep' || $key === 'none' ? '' : "
                            <input type='checkbox' value='{$value[$x]}' id='{$value[$x]}' name='" . $key . "[]}'>") . '
                        </div>';
    }
    $body .= '
                    </div>';
}

$genres_text .= main_div($body, 'top20');
$HTMLOUT .= "
            <tr>
                <td>{$lang['upload_genre']}</td>
                <td>$genres_text</td>
            </tr>";
