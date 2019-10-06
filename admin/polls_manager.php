<?php

declare(strict_types = 1);

use Delight\Auth\AuthError;
use Delight\Auth\NotLoggedInException;
use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
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
 * @throws AuthError
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function delete_poll($stdfoot)
{
    global $container, $site_config;

    $poll_stuffs = $container->get(Poll::class);
    $pollvoter_class = $container->get(PollVoter::class);
    if (!isset($_GET['pid']) || !is_valid_id((int) $_GET['pid'])) {
        stderr(_('Error'), _('There is no poll with that ID!'));
    }
    $pid = intval($_GET['pid']);
    if (!isset($_GET['sure'])) {
        stderr(_('USER WARNING'), "
        <div class='has-text-centered'>
            <h1>" . _('You are about to delete a poll forever!') . "</h1>
            <a href='javascript:history.back()' title='" . _('Cancel this operation') . "' class='button is-small right20'>
                " . _('Go Back') . "
            </a>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=polls_manager&amp;action=polls_manager&amp;mode=delete&amp;pid={$pid}&amp;sure=1' class='button is-small'>
                " . _('Delete Sure?') . '
            </a>
        </div>');
    }
    $poll_stuffs->delete($pid);
    $pollvoter_class->delete($pid);
    $pollvoter_class->delete_users_cache();
    show_poll_archive($stdfoot);
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function update_poll()
{
    global $container, $CURUSER;

    $poll_stuffs = $container->get(Poll::class);
    $pollvoter_class = $container->get(PollVoter::class);
    $session = $container->get(Session::class);
    if (!isset($_POST['pid']) || !is_valid_id((int) $_POST['pid'])) {
        stderr(_('Error'), _('There is no poll with that ID!'));
    }
    $pid = intval($_POST['pid']);
    if (!isset($_POST['poll_question']) || empty($_POST['poll_question'])) {
        stderr(_('Error'), _('There is no title defined!'));
    }
    $poll_title = htmlsafechars(strip_tags($_POST['poll_question']));
    $poll_data = makepoll();
    $total_votes = isset($poll_data['total_votes']) ? (int) $poll_data['total_votes'] : 0;
    unset($poll_data['total_votes']);
    if (!is_array($poll_data) || !count($poll_data)) {
        stderr(_('Error'), _('There was no data sent'));
    }
    $set = [
        'choices' => json_encode($poll_data),
        'starter_id' => $CURUSER['id'],
        'votes' => $total_votes,
        'poll_question' => $poll_title,
    ];
    $result = $poll_stuffs->update($set, $pid);
    $pollvoter_class->delete_users_cache();
    if (!$result) {
        $msg = _('An Error Occured!');
    } else {
        $msg = _('Groovy, everything went hunky dory!');
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
    global $container, $CURUSER;

    $poll_stuffs = $container->get(Poll::class);
    $pollvoter_class = $container->get(PollVoter::class);
    $session = $container->get(Session::class);
    if (!isset($_POST['poll_question']) || empty($_POST['poll_question'])) {
        stderr(_('Error'), _('There is no title defined!'));
    }
    $poll_title = htmlsafechars(strip_tags($_POST['poll_question']));
    $poll_data = makepoll();
    if (!is_array($poll_data) || !count($poll_data)) {
        stderr(_('Error'), _('There was no data sent'));
    }

    $values = [
        'start_date' => TIME_NOW,
        'choices' => json_encode($poll_data),
        'starter_id' => $CURUSER['id'],
        'votes' => 0,
        'poll_question' => $poll_title,
    ];
    $result = $poll_stuffs->insert($values);
    $pollvoter_class->delete_users_cache();
    if (!$result) {
        $msg = _('An Error Occured!');
    } else {
        $msg = _('Groovy, everything went hunky dory!');
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
    global $site_config;

    $poll_box = poll_box($site_config['poll']['max_questions'], $site_config['poll']['max_choices_per_question'], 'poll_new');
    $title = _('Add New Poll');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($poll_box) . stdfoot($stdfoot);
}

/**
 * @param $stdfoot
 *
 * @throws Exception
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws InvalidManipulation
 *
 * @return mixed
 */
function edit_poll_form($stdfoot)
{
    global $container, $site_config;

    $poll_stuffs = $container->get(Poll::class);
    $poll_questions = '';
    $poll_multi = '';
    $poll_choices = '';
    $poll_votes = '';
    $poll_data = $poll_stuffs->get((int) $_GET['pid']);
    if (empty($poll_data)) {
        return _('No poll with that ID');
    }
    $poll_answers = $poll_data['choices'] ? json_decode($poll_data['choices'], true) : [];
    foreach ($poll_answers as $question_id => $data) {
        $poll_questions .= "\t{$question_id} : '" . str_replace("'", '&#39;', $data['question']) . "',\n";
        $data['multi'] = isset($data['multi']) ? (int) $data['multi'] : 0;
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

    $title = _('Edit Poll');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($poll_box) . stdfoot($stdfoot);
}

/**
 * @param $stdfoot
 *
 * @throws AuthError
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 */
function show_poll_archive($stdfoot)
{
    global $container, $site_config;

    $poll_stuffs = $container->get(Poll::class);
    $HTMLOUT = '';
    $polls = $poll_stuffs->get_all();
    if (empty($polls)) {
        $HTMLOUT = main_div("
        <h1 class='has-text-centered'>" . _('No polls defined') . "</h1>
        <div class='has-text-centered'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=polls_manager&amp;action=polls_manager&amp;mode=new' class='button is-small margin20'>
                " . _('Add New Poll') . '
            </a>
        </div>');
    } else {
        $HTMLOUT .= "
        <h1 class='has-text-centered'>" . _('Manage Polls') . "</h1>
        <div class='has-text-centered'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=polls_manager&amp;action=polls_manager&amp;mode=new' class='button is-small margin20'>
                " . _('Add New Poll') . '
            <a>
        </div>';
        $heading = '
        <tr>
            <th>' . _('ID') . '</th>
            <th>' . _('Question') . '</th>
            <th>' . _('No. Votes') . '</th>
            <th>' . _('Date') . '</th>
            <th>' . _('Starter') . '</th>
            <th></th>
        </tr>';
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
                        <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=polls_manager&amp;action=polls_manager&amp;mode=edit&amp;pid=" . (int) $row['pid'] . "' title='" . _('Edit') . "' class='tooltipper'>
                            <i class='icon-edit icon has-text-info' aria-hidden='true'></i>
                        </a>
                    </span>
                    <span>
                        <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=polls_manager&amp;action=polls_manager&amp;mode=delete&amp;pid=" . (int) $row['pid'] . "' title='" . _('Delete') . "' class='tooltipper'>
                            <i class='icon-trash-empty icon has-text-danger' aria-hidden='true'></i>
                        </a>
                    </span>
                </div>
            </td>
        </tr>";
        }
        $HTMLOUT .= main_table($body, $heading);
    }
    $title = _('Poll Archive');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
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
    global $site_config;

    $pid = isset($_GET['pid']) ? (int) $_GET['pid'] : 0;
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
        var html_add_question = \"<a href='#' title='" . _('Add Poll Question') . "' onclick='return poll_add_question()' class='button is-small bottom20'>" . _('Add Poll Question') . "</a>\";
        var html_add_choice = \"<li><a href='#' title='" . _('Add Poll Choice') . "' onclick='return poll_add_choice(\"+'\"'+'<%1>'+'\"'+\")' class='button is-small bottom20'>" . _('Add Poll Choice') . "</a></li>\";
        var html_question_box = \"<input type='text' id='question_<%1>' name='question[<%1>]' class='input w-100 bottom20' value='<%2>'> <a href='#' class='button is-small bottom20' onclick='return poll_remove_question(\"+'\"'+'<%1>'+'\"'+\")'>" . _('Remove Question') . "</a><br><input class='checkbox bottom20' type='checkbox' id='multi_<%1>' name='multi[<%1>]' value='1' <%3>><span>" . _('Multiple choice question? (Allows users to select more than one choice)') . "</span>\";
        var html_votes_box = \"<input type='text' id='votes_<%1>_<%2>' name='votes[<%1>_<%2>]' class='input w-10 bottom20 right10' value='<%3>'>\";
        var html_choice_box = \"<li><input type='text' id='choice_<%1>_<%2>' name='choice[<%1>_<%2>]' class='input w-20 bottom20 right10' value='<%3>'><%4> <a href='#' class='button is-small bottom20' onclick='return poll_remove_choice(\"+'\"'+'<%1>_<%2>'+'\"'+\")'>" . _('Remove Choice') . "</a></li>\";
        var html_choice_wrap = \"<ol class='left20'><%1></ol>\";
        var html_question_wrap = \"<div><%1></div>\";
        var html_stat_wrap = \"<br><div><%1></div>\";

        // Lang elements
        var js_lang_confirm = \"" . _('Please confirm this action') . '";
        var poll_stat_lang = "' . _('You are allowed') . ' <%1> ' . _('more question(s) with') . ' <%2> ' . _('choices per question.') . "\";
    </script>

    


    <h1 class='has-text-centered'>" . _('Editing Poll') . "</h1>
    <form id='postingform' action='{$_SERVER['PHP_SELF']}?tool=polls_manager&amp;action=polls_manager' method='post' name='inputform' enctype='multipart/form-data' accept-charset='utf-8'>
        <input type='hidden' name='mode' value='$form_type'>
        <input type='hidden' name='pid' value='$pid'>
        <div>
            <fieldset class='bottom20'>
                <legend>" . _('Poll Title') . "</legend>
                <input type='text' name='poll_question' value='$poll_question' class='w-100 bottom20'>
            </fieldset>

            <fieldset class='bottom20'>
                <legend>" . _('Poll Content') . "</legend>
                <div id='poll-box-main' class=''></div>
            </fieldset>

            <fieldset class='bottom20'>
                <legend>" . _('Poll Info') . "</legend>
                <div id='poll-box-stat' class=''></div>
            </fieldset>
            <div class='has-text-centered'>
                <input type='submit' name='submit' value='" . _('Post Poll') . "' class='button is-small right20'>
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=polls_manager&amp;action=polls_manager' class='button is-small'>" . _('Cancel') . '</a>
            </div>
        </div>
    </form>';

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
