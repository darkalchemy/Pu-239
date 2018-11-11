<?php

$genres = [
    'Movie',
    'TV',
    'Music',
    'Game',
    'Apps',
];

$HTMLOUT .= '
    <tr>
        <td>Genre</td>
        <td>';
$body = "
            <div class='columns'>" . (!empty($row['newgenre']) ? "
                <div class='column'>
                    <div class='level-center-center top20 bottom20'>
                        <label for='keep_it' class='right5'>No Changes</label>
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
                <div class='column'>
                    <div class='level-center-center top20 bottom20'>
                        <label for='is_none' class='right5'>None</label>
                        <input type='radio' name='genre' id='is_none' value='none'" . (empty($row['newgenre']) ? ' checked' : '') . '>
                    </div>
                </div>
            </div>';
$HTMLOUT .= main_div($body, 'bottom20');
$genres = [
    'keep' => [(!empty($row['newgenre']) ? htmlsafechars($row['newgenre']) : '')],
    'movie' => [
        'Action',
        'Adult',
        'Adventure',
        'Comedy',
        'Documentary',
        'Drame',
        'Family',
        'Horror',
        'Thriller',
        'Sci-fi',
    ],
    'tv' => [
        'Action',
        'Adult',
        'Adventure',
        'Comedy',
        'Documentary',
        'Drame',
        'Family',
        'Horror',
        'Thriller',
        'Sci-fi',
    ],
    'music' => [
        'Hip Hop',
        'Rock',
        'Pop',
        'House',
        'Techno',
        'Commercial',
    ],
    'game' => [
        'FPS',
        'Strategy',
        'Adventure',
        '3rd Person',
        'Acton',
    ],
    'apps' => [
        'Burning',
        'Encoding',
        'Anti-Virus',
        'Office',
        'OS',
        'Misc',
        'Image',
    ],
    'none' => ['None'],
];

$body = '';
foreach ($genres as $key => $value) {
    $class = 'is_hidden';
    if (!empty($row['newgenre']) && $key === 'keep' || empty($row['newgenre']) && $key === 'none') {
        $class = '';
    }
    $body .= "
            <div id='$key' class='$class level-center top10 bottom10'>";
    for ($x = 0; $x < count($value); ++$x) {
        $body .= "
                <div id='$key' class='level-center'>
                    <label for='{$value[$x]}' class='right5'>{$value[$x]}</label>" . ($key === 'keep' || $key === 'none' ? '' : "
                    <input type='checkbox' value='{$value[$x]}' id='{$value[$x]}' name='" . $key . "[]}'>") . '
                </div>';
    }
    $body .= '
            </div>';
}

$HTMLOUT .= main_div($body);
/*
$HTMLOUT .= "
            <div id='movie' class='is_hidden'>
            <table class='table table-bordered'>
                <tr>";
for ($x = 0; $x < count($movie); ++$x) {
    $HTMLOUT .= "
                    <td class='rowhead'>
                        <div class='level-center top10 bottom10'>
                            <label for='{$movie[$x]}'>{$movie[$x]}</label>
                            <input type='checkbox' value='{$movie[$x]}' id='{$movie[$x]}' name='{movie[]}'>
                        </div>
                    </td>";
}
$HTMLOUT .= "
                </tr>
            </table>
            </div>";
$HTMLOUT .= "
            <div id='music' class='is_hidden'>
            <table class='table table-bordered'>
                <tr>";
for ($x = 0; $x < count($music); ++$x) {
    $HTMLOUT .= "
                    <td class='rowhead'>
                        <div class='level-center top10 bottom10'>
                            <label for='{$music[$x]}'>{$music[$x]}</label>
                            <input type='checkbox' id='{$music[$x]}' value='{$music[$x]}' name='{music[]}'>
                        </div>
                    </td>";
}
$HTMLOUT .= "
                </tr>
            </table>
            </div>";
$HTMLOUT .= "
            <div id='game' class='is_hidden'>
            <table class='table table-bordered'>
                <tr>";
for ($x = 0; $x < count($game); ++$x) {
    $HTMLOUT .= "
                    <td class='rowhead'>
                        <div class='level-center top10 bottom10'>
                            <label for='{$game[$x]}'>{$game[$x]}</label>
                            <input type='checkbox' id='{$game[$x]}' value='{$game[$x]}' name='{game[]}'>
                        </div>
                    </td>";
}
$HTMLOUT .= "
                </tr>
            </table>
            </div>";
$HTMLOUT .= "
            <div id='apps' class='is_hidden'>
            <table class='table table-bordered'>
                <tr>";
for ($x = 0; $x < count($apps); ++$x) {
    $HTMLOUT .= "
                    <td class='rowhead'>
                        <div class='level-center top10 bottom10'>
                            <label for='{$apps[$x]}'>{$apps[$x]}</label>
                            <input type='checkbox' id='{$apps[$x]}' value='{$apps[$x]}' name='{apps[]}'>
                        </div>
                    </td>";
}
$HTMLOUT .= "
                </tr>
            </table>
            </div>";
$none = [
    'None',
];
$HTMLOUT .= "
            <div id='none'>
            <table class='table table-bordered'>
                <tr>";
for ($x = 0; $x < count($none); ++$x) {
    $HTMLOUT .= "
                    <td class='rowhead'>
                        <div class='level-center top10 bottom10'>
                            <span id='has-none'>" . (!empty($row['newgenre']) ? htmlsafechars($row['newgenre']) : 'None') . "</span>
                        </div>
                    </td>";
}

$HTMLOUT .= "
                </tr>
            </table>
            </div>
        </td>
    </tr>";
*/
