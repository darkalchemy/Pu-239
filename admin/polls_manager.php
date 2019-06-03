<?php

declare(strict_types = 1);

use Delight\Auth\AuthError;
use Delight\Auth\NotLoggedInException;
use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Poll;
use Pu239\PollVoter;
use Pu239\Session;
use Spatie\Image\Exceptions\InvalidManipulation;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$stdfoot = [
    'js' => [
        get_file_name('pollsmanager_js'),
    ],
];

$lang = array_merge($lang, load_language('ad_poll_manager'));
$params = array_merge($_GET, $_POST);
$params['mode'] = isset($params['mode']) ? $params['mode'] : '';

switch ($params['mode']) {
    case 'delete':
        delete_poll($stdfoot);
        break;

    case 'edit':
        edit_poll_form($stdfoot);
        break;

    case 'new':
        show_poll_form($stdfoot);
        break;

    case 'poll_new':
        insert_new_poll();
        break;

    case 'poll_update':
        update_poll();
        break;

    default:
        show_poll_archive($stdfoot);
        break;
}

/**
 * @param $stdfoot
 *
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws AuthError
 * @throws NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 */
function delete_poll($stdfoot)
{
    global $container, $lang, $site_config;

    $poll_stuffs = $container->get(Poll::class);
    $pollvoter_stuffs = $container->get(PollVoter::class);
    if (!isset($_GET['pid']) || !is_valid_id($_GET['pid'])) {
        stderr($lang['poll_dp_usr_err'], $lang['poll_dp_no_poll']);
    }
    $pid = intval($_GET['pid']);
    if (!isset($_GET['sure'])) {
        stderr($lang['poll_dp_usr_warn'], "
        <div class='has-text-centered'>
            <h1>{$lang['poll_dp_forever']}</h1>
            <a href='javascript:history.back()' title='{$lang['poll_dp_cancel']}' class='button is-small right20'>
                {$lang['poll_dp_back']}
            </a>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=polls_manager&amp;action=polls_manager&amp;mode=delete&amp;pid={$pid}&amp;sure=1' class='button is-small'>
                {$lang['poll_dp_delete2']}
            </a>
        </div>");
    }
    $poll_stuffs->delete($pid);
    $pollvoter_stuffs->delete($pid);
    $pollvoter_stuffs->delete_users_cache();
    show_poll_archive($stdfoot);
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function update_poll()
{
    global $container, $lang, $CURUSER;

    $poll_stuffs = $container->get(Poll::class);
    $pollvoter_stuffs = $container->get(PollVoter::class);
    $session = $container->get(Session::class);
    if (!isset($_POST['pid']) || !is_valid_id($_POST['pid'])) {
        stderr($lang['poll_up_usr_err'], $lang['poll_up_no_poll']);
    }
    $pid = intval($_POST['pid']);
    if (!isset($_POST['poll_question']) || empty($_POST['poll_question'])) {
        stderr($lang['poll_up_usr_err'], $lang['poll_up_no_title']);
    }
    $poll_title = htmlsafechars(strip_tags($_POST['poll_question']));
    $poll_data = makepoll();
    $total_votes = isset($poll_data['total_votes']) ? intval($poll_data['total_votes']) : 0;
    unset($poll_data['total_votes']);
    if (!is_array($poll_data) || !count($poll_data)) {
        stderr($lang['poll_up_sys_err'], $lang['poll_up_no_data']);
    }
    $set = [
        'choices' => serialize($poll_data),
        'starter_id' => $CURUSER['id'],
        'votes' => $total_votes,
        'poll_question' => $poll_title,
    ];
    $result = $poll_stuffs->update($set, $pid);
    $pollvoter_stuffs->delete_users_cache();
    if (!$result) {
        $msg = $lang['poll_up_error'];
    } else {
        $msg = $lang['poll_up_worked'];
    }
    $session->set('is-info', $msg);
    header("Location: {$_SERVER['PHP_SELF']}?tool=polls_manager&action=polls_manager");
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function insert_new_poll()
{
    global $container, $lang, $CURUSER;

    $poll_stuffs = $container->get(Poll::class);
    $pollvoter_stuffs = $container->get(PollVoter::class);
    $session = $container->get(Session::class);
    if (!isset($_POST['poll_question']) || empty($_POST['poll_question'])) {
        stderr($lang['poll_inp_usr_err'], $lang['poll_inp_no_title']);
    }
    $poll_title = htmlsafechars(strip_tags($_POST['poll_question']));
    $poll_data = makepoll();
    if (!is_array($poll_data) || !count($poll_data)) {
        stderr($lang['poll_inp_sys_err'], $lang['poll_inp_no_data']);
    }

    $values = [
        'start_date' => TIME_NOW,
        'choices' => serialize($poll_data),
        'starter_id' => $CURUSER['id'],
        'votes' => 0,
        'poll_question' => $poll_title,
    ];
    $result = $poll_stuffs->insert($values);
    $pollvoter_stuffs->delete_users_cache();
    if (!$result) {
        $msg = $lang['poll_inp_error'];
    } else {
        $msg = $lang['poll_inp_worked'];
    }
    $session->set('is-info', $msg);
    header("Location: {$_SERVER['PHP_SELF']}?tool=polls_manager&action=polls_manager");
}

/**
 * @param $stdfoot
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws InvalidManipulation
 * @throws Exception
 */
function show_poll_form($stdfoot)
{
    global $site_config, $lang;

    $poll_box = poll_box($site_config['poll']['max_questions'], $site_config['poll']['max_choices_per_question'], 'poll_new');
    echo stdhead($lang['poll_spf_stdhead']) . wrapper($poll_box) . stdfoot($stdfoot);
}

/**
 * @param $stdfoot
 *
 * @return mixed
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws InvalidManipulation
 * @throws Exception
 *
 * @throws DependencyException
 */
function edit_poll_form($stdfoot)
{
    global $container, $lang, $site_config;

    $poll_stuffs = $container->get(Poll::class);
    $poll_questions = '';
    $poll_multi = '';
    $poll_choices = '';
    $poll_votes = '';
    $poll_data = $poll_stuffs->get($_GET['pid']);
    if (empty($poll_data)) {
        return $lang['poll_epf_no_poll'];
    }
    $poll_answers = $poll_data['choices'] ? unserialize(stripslashes($poll_data['choices'])) : [];
    foreach ($poll_answers as $question_id => $data) {
        $poll_questions .= "\t{$question_id} : '" . str_replace("'", '&#39;', $data['question']) . "',\n";
        $data['multi'] = isset($data['multi']) ? intval($data['multi']) : 0;
        $poll_multi .= "\t{$question_id} : '" . $data['multi'] . "',\n";
        foreach ($data['choice'] as $choice_id => $text) {
            $choice = $text;
            $votes = intval($data['votes'][$choice_id]);
            $poll_choices .= "\t'{$question_id}_{$choice_id}' : '" . str_replace("'", '&#39;', $choice) . "',\n";
            $poll_votes .= "\t'{$question_id}_{$choice_id}' : '" . $votes . "',\n";
        }
    }
    $poll_questions = preg_replace("#,(\n)?$#", '\\1', $poll_questions);
    $poll_choices = preg_replace("#,(\n)?$#", '\\1', $poll_choices);
    $poll_multi = preg_replace("#,(\n)?$#", '\\1', $poll_multi);
    $poll_votes = preg_replace("#,(\n)?$#", '\\1', $poll_votes);
    $poll_question = $poll_data['poll_question'];
    $show_open = $poll_data['choices'] ? 1 : 0;
    $poll_box = poll_box($site_config['poll']['max_questions'], $site_config['poll']['max_choices_per_question'], 'poll_update', $poll_questions, $poll_choices, $poll_votes, $show_open, $poll_question, $poll_multi);

    echo stdhead($lang['poll_epf_stdhead']) . wrapper($poll_box) . stdfoot($stdfoot);
}

/**
 * @param $stdfoot
 *
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws AuthError
 * @throws NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 */
function show_poll_archive($stdfoot)
{
    global $container, $lang, $site_config;

    $poll_stuffs = $container->get(Poll::class);
    $HTMLOUT = '';
    $polls = $poll_stuffs->get_all();
    if (empty($polls)) {
        $HTMLOUT = main_div("
        <h1 class='has-text-centered'>{$lang['poll_spa_no_polls']}</h1>
        <div class='has-text-centered'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=polls_manager&amp;action=polls_manager&amp;mode=new' class='button is-small margin20'>
                {$lang['poll_spa_add']}
            </a>
        </div>");
    } else {
        $HTMLOUT .= "
        <h1 class='has-text-centered'>{$lang['poll_spa_manage']}</h1>
        <div class='has-text-centered'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=polls_manager&amp;action=polls_manager&amp;mode=new' class='button is-small margin20'>
                {$lang['poll_spa_add']}
            <a>
        </div>";
        $heading = "
        <tr>
            <th>{$lang['poll_spa_id']}</th>
            <th>{$lang['poll_spa_question']}</th>
            <th>{$lang['poll_spa_count']}</th>
            <th>{$lang['poll_spa_date']}</th>
            <th>{$lang['poll_spa_starter']}</th>
            <th></th>
        </tr>";
        $body = '';
        foreach ($polls as $row) {
            $row['start_date'] = get_date((int) $row['start_date'], 'DATE');
            $body .= '
        <tr>
            <td>' . (int) $row['pid'] . '</td>
            <td>' . htmlsafechars($row['poll_question']) . '</td>
            <td>' . (int) $row['votes'] . '</td>
            <td>' . htmlsafechars($row['start_date']) . '</td>
            <td>
                ' . format_username((int) $row['starter_id']) . "</a>
            </td>
            <td>
                <div class='level-center'>
                    <span>
                        <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=polls_manager&amp;action=polls_manager&amp;mode=edit&amp;pid=" . (int) $row['pid'] . "' title='{$lang['poll_spa_edit']}' class='tooltipper'>
                            <i class='icon-edit icon'></i>
                        </a>
                    </span>
                    <span>
                        <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=polls_manager&amp;action=polls_manager&amp;mode=delete&amp;pid=" . (int) $row['pid'] . "' title='{$lang['poll_spa_delete']}' class='tooltipper'>
                            <i class='icon-trash-empty icon has-text-danger'></i>
                        </a>
                    </span>
                </div>
            </td>
        </tr>";
        }
        $HTMLOUT .= main_table($body, $heading);
    }
    echo stdhead($lang['poll_spa_stdhead']) . wrapper($HTMLOUT) . stdfoot($stdfoot);
}

/**
 * @param string $max_poll_questions
 * @param string $max_poll_choices
 * @param string $form_type
 * @param string $poll_questions
 * @param string $poll_choices
 * @param string $poll_votes
 * @param string $show_open
 * @param string $poll_question
 * @param string $poll_multi
 *
 * @return string
 */
function poll_box($max_poll_questions = '', $max_poll_choices = '', $form_type = '', $poll_questions = '', $poll_choices = '', $poll_votes = '', $show_open = '', $poll_question = '', $poll_multi = '')
{
    global $lang, $site_config;

    $pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
    $form_type = $form_type != '' ? $form_type : 'poll_update';
    $HTMLOUT = "
    <script>
        var showfullonload = parseInt(\"{$show_open}\");

        // Questions
        var poll_questions = {{$poll_questions}};
        var poll_choices = {{$poll_choices}};
        var poll_votes = {{$poll_votes}};
        var poll_multi = {{$poll_multi}};

        // Setting elements
        var max_poll_questions = parseInt(\"{$max_poll_questions}\");
        var max_poll_choices = parseInt(\"{$max_poll_choices}\");

        // HTML elements
        var html_add_question = \"<a href='#' title='{$lang['poll_pb_add_q']}' onclick='return poll_add_question()' class='button is-small bottom20'>{$lang['poll_pb_add_q']}</a>\";
        var html_add_choice = \"<li><a href='#' title='{$lang['poll_pb_add_c']}' onclick='return poll_add_choice(\"+'\"'+'<%1>'+'\"'+\")' class='button is-small bottom20'>{$lang['poll_pb_add_c']}</a></li>\";
        var html_question_box = \"<input type='text' id='question_<%1>' name='question[<%1>]' class='input w-100 bottom20' value='<%2>'> <a href='#' class='button is-small bottom20' onclick='return poll_remove_question(\"+'\"'+'<%1>'+'\"'+\")'>{$lang['poll_pb_remove_q']}</a><br><input class='checkbox bottom20' type='checkbox' id='multi_<%1>' name='multi[<%1>]' value='1' <%3>><span>{$lang['poll_pb_multiple']}</span>\";
        var html_votes_box = \"<input type='text' id='votes_<%1>_<%2>' name='votes[<%1>_<%2>]' class='input w-10 bottom20 right10' value='<%3>'>\";
        var html_choice_box = \"<li><input type='text' id='choice_<%1>_<%2>' name='choice[<%1>_<%2>]' class='input w-20 bottom20 right10' value='<%3>'><%4> <a href='#' class='button is-small bottom20' onclick='return poll_remove_choice(\"+'\"'+'<%1>_<%2>'+'\"'+\")'>{$lang['poll_pb_rem_choice']}</a></li>\";
        var html_choice_wrap = \"<ol class='left20'><%1></ol>\";
        var html_question_wrap = \"<div><%1></div>\";
        var html_stat_wrap = \"<br><div><%1></div>\";

        // Lang elements
        var js_lang_confirm = \"{$lang['poll_pb_confirm']}\";
        var poll_stat_lang = \"{$lang['poll_pb_allowed']} <%1> {$lang['poll_pb_more']} <%2>  {$lang['poll_pb_choices']}\";
    </script>
    <h1 class='has-text-centered'>{$lang['poll_pb_editing']}</h1>
    <form id='postingform' action='{$_SERVER['PHP_SELF']}?tool=polls_manager&amp;action=polls_manager' method='post' name='inputform' enctype='multipart/form-data' accept-charset='utf-8'>
        <input type='hidden' name='mode' value='$form_type'>
        <input type='hidden' name='pid' value='$pid'>
        <div>
            <fieldset class='bottom20'>
                <legend>{$lang['poll_pb_title']}</legend>
                <input type='text' name='poll_question' value='$poll_question' class='w-100 bottom20'>
            </fieldset>

            <fieldset class='bottom20'>
                <legend>{$lang['poll_pb_content']}</legend>
                <div id='poll-box-main' class=''></div>
            </fieldset>

            <fieldset class='bottom20'>
                <legend>{$lang['poll_pb_info']}</legend>
                <div id='poll-box-stat' class=''></div>
            </fieldset>
            <div class='has-text-centered'>
                <input type='submit' name='submit' value='{$lang['poll_pb_post']}' class='button is-small right20'>
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=polls_manager&amp;action=polls_manager' class='button is-small'>{$lang['poll_pb_cancel']}</a>
            </div>
        </div>
    </form>";

    return main_div($HTMLOUT, null, 'padding20');
}

/**
 * @return array
 */
function makepoll()
{
    global $site_config;

    $questions = [];
    $choices_count = 0;
    $poll_total_votes = 0;
    if (isset($_POST['question']) && is_array($_POST['question']) && count($_POST['question'])) {
        foreach ($_POST['question'] as $id => $q) {
            if (!$q || !$id) {
                continue;
            }
            $questions[$id]['question'] = htmlsafechars(strip_tags($q));
        }
    }
    if (isset($_POST['multi']) && is_array($_POST['multi']) && count($_POST['multi'])) {
        foreach ($_POST['multi'] as $id => $q) {
            if (!$q || !$id) {
                continue;
            }
            $questions[$id]['multi'] = intval($q);
        }
    }
    if (isset($_POST['choice']) && is_array($_POST['choice']) && count($_POST['choice'])) {
        foreach ($_POST['choice'] as $mainid => $choice) {
            list($question_id, $choice_id) = explode('_', $mainid);
            $question_id = intval($question_id);
            $choice_id = intval($choice_id);
            if (!$question_id || !isset($choice_id)) {
                continue;
            }
            if (!$questions[$question_id]['question']) {
                continue;
            }
            $questions[$question_id]['choice'][$choice_id] = htmlsafechars(strip_tags($choice));
            $_POST['votes'] = isset($_POST['votes']) ? $_POST['votes'] : 0;
            $questions[$question_id]['votes'][$choice_id] = intval($_POST['votes'][$question_id . '_' . $choice_id]);
            $poll_total_votes += $questions[$question_id]['votes'][$choice_id];
        }
    }
    foreach ($questions as $id => $data) {
        if (!is_array($data['choice']) || !count($data['choice'])) {
            unset($questions[$id]);
        } else {
            $choices_count += intval(count($data['choice']));
        }
    }
    if (count($questions) > $site_config['poll']['max_questions']) {
        die('poll_to_many');
    }
    if ($choices_count > ($site_config['poll']['max_questions'] * $site_config['poll']['max_choices_per_question'])) {
        die('poll_to_many');
    }
    if (isset($_POST['mode']) && $_POST['mode'] == 'poll_update') {
        $questions['total_votes'] = $poll_total_votes;
    }

    return $questions;
}
