<?php

declare(strict_types = 1);

$genres = [
    _('Movie'),
    _('TV'),
    _('Music'),
    _('Games'),
    _('Apps'),
    _('None'),
];

$body = "
                    <div class='columns is-gapless'>" . (!empty($row['newgenre']) ? "
                        <div class='column'>
                            <div class='level-center-center top20 bottom20'>
                                <label for='keep_it' class='right5'>" . _('No Changes') . "</label>
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
$body .= '
                    </div>';
$genres_text = main_div($body);
$genres = [
    'keep' => [(!empty($row['newgenre']) ? htmlsafechars($row['newgenre']) : '')],
    'movie' => [
        _('Action'),
        _('Adult'),
        _('Adventure'),
        _('Comedy'),
        _('Crime'),
        _('Documentary'),
        _('Drama'),
        _('Family'),
        _('Fantasy'),
        _('Historical'),
        _('Horror'),
        _('Mystery'),
        _('Political'),
        _('Reality'),
        _('Sci-fi'),
        _('Slasher'),
        _('Suspense'),
        _('Thriller'),
        _('Western'),
    ],
    'tv' => [
        _('Action'),
        _('Adult'),
        _('Adventure'),
        _('Comedy'),
        _('Crime'),
        _('Documentary'),
        _('Drama'),
        _('Family'),
        _('Fantasy'),
        _('Historical'),
        _('Horror'),
        _('Mystery'),
        _('Political'),
        _('Reality'),
        _('Sci-fi'),
        _('Slasher'),
        _('Suspense'),
        _('Thriller'),
        _('Western'),
    ],
    'music' => [
        _('Hip Hop'),
        _('Rock'),
        _('Pop'),
        _('House'),
        _('Techno'),
        _('Commercial'),
    ],
    'game' => [
        _('FPS'),
        _('Strategy'),
        _('Adventure'),
        _('3rd Person'),
        _('Action'),
    ],
    'apps' => [
        _('Burning'),
        _('Encoding'),
        _('Anti-Virus'),
        _('Office'),
        _('OS'),
        _('Misc'),
        _('Image'),
    ],
    'none' => [
        _('None'),
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
$HTMLOUT .= '
            <tr>
                <td>' . _('Genre') . "</td>
                <td>$genres_text</td>
            </tr>";
