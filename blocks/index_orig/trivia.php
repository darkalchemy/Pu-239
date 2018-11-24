<?php

require_once INCL_DIR . 'trivia_functions.php';
global $lang, $site_config, $fluent, $session;

$table = trivia_table();
$qid = $table['qid'];
$gamenum = $table['gamenum'];
$table = $table['table'];

$table = !empty($table) ? $table : main_div('No one has attempted any questions, yet.');

$buttons = "
    <ul class='level-center'>
        <li class='top20'>
            <button id='button' onclick=\"get_trivia_question()\" class='button is-small'>Get Trivia Question</button>
        </li>
        <li  class='top20'>
            <a href='{$site_config['baseurl']}/trivia_results.php' target='_top' class='button is-small'>Get Trivia Results</a>
        </li>
    </ul>";
$content = "
        <div class='bordered' style='display: none;'>
            <div class='alt_bordered bg-00'>
                <div id='content'></div>
            </div>
        </div>";

$trivia .= "
    <a id='trivia-hash'></a>
    <fieldset id='trivia' class='header'>
        <legend class='flipper has-text-primary'><i class='icon-down-open size_2' aria-hidden='true'></i>{$lang['index_trivia']}</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00'>
                <div id='trivia_display' data-csrf='" . $session->get('csrf_token') . "' data-qid='{$qid}' data-gamenum='{$gamenum}'>
                    <div id='trivia_content' class='has-text-centered'>{$table}{$content}</div>
                    <div id='trivia_buttons'>{$buttons}</div>
                </div>
            </div>
        </div>
    </fieldset>";
