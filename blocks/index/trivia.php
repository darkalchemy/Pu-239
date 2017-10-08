<?php
$HTMLOUT .= "
    <a id='trivia-hash'></a>
    <fieldset id='trivia' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_trivia']}</legend>
        <div class='bordered padleft10 padright10 bottom10'>
            <div class='alt_bordered transparent text-center'>
                <iframe src='./trivia.php' id='triviabox' name='triviabox'></iframe>
            </div>
        </div>
    </fieldset>";
