<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Session;

require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_pager.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_triviaconfig'));
$search = '';
global $container, $site_config;

$fluent = $container->get(Database::class);
$session = $container->get(Session::class);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $canswers = [
        'canswer1',
        'canswer2',
        'canswer3',
        'canswer4',
        'canswer5',
    ];

    $types = [
        'search',
        'insert',
        'update',
        'delete',
    ];
    foreach ($types as $type) {
        if (isset($_POST[$type])) {
            if ($type === 'update') {
                $set = $_POST;
                $id = $set['id'];
                unset($set['update'], $set['id']);
                foreach ($canswers as $canswer) {
                    if (isset($set[$canswer])) {
                        $set['canswer'] = str_replace('c', '', $canswer);
                        unset($set[$canswer]);
                    }
                }
                if (empty($set['canswer'])) {
                    $session->set('is-warning', $lang['triviaconfig_noanswer']);
                } else {
                    $fluent->update('triviaq')
                           ->set($set)
                           ->where('qid = ?', $id)
                           ->execute();
                }
            } elseif ($type === 'delete' && isset($_POST['id']) && is_numeric($_POST['id'])) {
                $fluent->deleteFrom('triviaq')
                       ->where('qid = ?', $_POST['id'])
                       ->execute();
                $session->set('is-success', sprintf($lang['triviaconfig_deleted'], $_POST['id']));
            } elseif ($type === 'insert') {
                $values = $_POST;
                unset($values['Add'], $values['insert']);
                foreach ($canswers as $canswer) {
                    if (isset($values[$canswer])) {
                        $values['canswer'] = str_replace('c', '', $canswer);
                        unset($values[$canswer]);
                    }
                }
                if (empty($values['canswer'])) {
                    $session->set('is-warning', $lang['triviaconfig_noanswer']);
                } else {
                    $newid = $fluent->insertInto('triviaq')
                                    ->values($values)
                                    ->execute();
                    if (!empty($newid)) {
                        $session->set('is-success', sprintf($lang['triviaconfig_inserted'], $newid));
                    }
                }
            } elseif ($type === 'search') {
                $search = $_POST['keywords'];
                $count = $fluent->from('triviaq')
                                ->select(null)
                                ->select('COUNT(qid) AS count')
                                ->where('MATCH (question, answer1, answer2, answer3, answer4, answer5) AGAINST (? IN NATURAL LANGUAGE MODE)', $search)
                                ->fetch('count');

                $pager = pager(15, $count, "{$site_config['paths']['baseurl']}/staffpanel.php?tool=trivia_config&");
                $questions = $fluent->from('triviaq')
                                    ->where('MATCH (question, answer1, answer2, answer3, answer4, answer5) AGAINST (? IN NATURAL LANGUAGE MODE)', $search)
                                    ->orderBy('qid')
                                    ->limit($pager['pdo']['limit'])
                                    ->offset($pager['pdo']['offset'])
                                    ->fetchAll();
            }
        }
    }
}

if (empty($search)) {
    $count = $fluent->from('triviaq')
                    ->select(null)
                    ->select('COUNT(qid) AS count')
                    ->fetch('count');

    $pager = pager(15, $count, "{$site_config['paths']['baseurl']}/staffpanel.php?tool=trivia_config&");
    $questions = $fluent->from('triviaq')
                        ->orderBy('qid')
                        ->limit($pager['pdo']['limit'])
                        ->offset($pager['pdo']['offset'])
                        ->fetchAll();
}

$HTMLOUT = "
        <h1 class='has-text-centered'>{$lang['triviaconfig_title']}</h1>
        <form action='{$_SERVER['PHP_SELF']}?tool=trivia_config' method='post' accept-charset='utf-8'>
            <div class='has-text-centered w-50'>
                <input type='text' class='search w-100' name='keywords' value='{$search}' placeholder='{$lang['triviaconfig_search_placeholder']}'>
                <input type='submit' name='search' class='button is-small margin20' value='{$lang['triviaconfig_search']}'>
            </div>
        </form>";

$body = "
        <form action='{$_SERVER['PHP_SELF']}?tool=trivia_config' method='post' accept-charset='utf-8'>
            <input type='hidden' name='Add' value='New'>
            <h2 class='has-text-centered'>{$lang['triviaconfig_add']}</h2>
            <p class='level-center-center'><input name='question' class='left20 w-75' type='text' value='' placeholder='{$lang['triviaconfig_question_placeholder']}'></p>
            <p class='level-center-center'><input name='canswer1' type='checkbox' class='right5'><input name='answer1' type='text' value='' class='w-75' placeholder='{$lang['triviaconfig_answer1']}'></p>
            <p class='level-center-center'><input name='canswer2' type='checkbox' class='right5'><input name='answer2' type='text' value='' class='w-75' placeholder='{$lang['triviaconfig_answer2']}'></p>
            <p class='level-center-center'><input name='canswer3' type='checkbox' class='right5'><input name='answer3' type='text' value='' class='w-75' placeholder='{$lang['triviaconfig_answer3']}'></p>
            <p class='level-center-center'><input name='canswer4' type='checkbox' class='right5'><input name='answer4' type='text' value='' class='w-75' placeholder='{$lang['triviaconfig_answer4']}'></p>
            <p class='level-center-center'><input name='canswer5' type='checkbox' class='right5'><input name='answer5' type='text' value='' class='w-75' placeholder='{$lang['triviaconfig_answer5']}'></p>
            <div class='margin20 has-text-centered'>
                <input type='submit' name='insert' class='button is-small' value='{$lang['triviaconfig_insert']}'>
            </div>
        </form>";
$HTMLOUT .= main_div($body, 'bottom20', 'padding20') . $pager['pagertop'];
foreach ($questions as $question) {
    $body = "
        <form action='{$_SERVER['PHP_SELF']}?tool=trivia_config' method='post' accept-charset='utf-8'>
            <input type='hidden' name='id' value='{$question['qid']}'>
            <h2 class='has-text-centered'>#{$question['qid']}: <span class='has-text-weight-bold is-warning'>{$question['question']}</span></h2>
            <p class='level-center-center'><input name='canswer1' type='checkbox'" . ($question['canswer'] === 'answer1' ? ' checked' : '') . " class='right5'><input name='answer1' type='text' class='w-75' value='{$question['answer1']}'></p>
            <p class='level-center-center'><input name='canswer2' type='checkbox'" . ($question['canswer'] === 'answer2' ? ' checked' : '') . " class='right5'><input name='answer2' type='text' class='w-75' value='{$question['answer2']}'></p>
            <p class='level-center-center'><input name='canswer3' type='checkbox'" . ($question['canswer'] === 'answer3' ? ' checked' : '') . " class='right5'><input name='answer3' type='text' class='w-75' value='{$question['answer3']}'></p>
            <p class='level-center-center'><input name='canswer4' type='checkbox'" . ($question['canswer'] === 'answer4' ? ' checked' : '') . " class='right5'><input name='answer4' type='text' class='w-75' value='{$question['answer4']}'></p>
            <p class='level-center-center'><input name='canswer5' type='checkbox'" . ($question['canswer'] === 'answer5' ? ' checked' : '') . " class='right5'><input name='answer5' type='text' class='w-75' value='{$question['answer5']}'></p>
            <div class='margin20 has-text-centered'>
                <input type='submit' name='update' class='button is-small' value='{$lang['triviaconfig_update']}'>
                <input type='submit' name='delete' class='button is-small' value='{$lang['triviaconfig_delete']}'>
            </div>
        </form>";
    $HTMLOUT .= main_div($body, 'top20', 'padding20');
}

$HTMLOUT .= $pager['pagerbottom'];

echo stdhead($lang['triviaconfig_title']) . wrapper($HTMLOUT) . stdfoot();
