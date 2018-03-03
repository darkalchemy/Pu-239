<?php
/**
 * @return string
 */
function parse_poll()
{
    global $CURUSER, $site_config, $cache;

    $htmlout     = '';
    $check       = 0;
    $poll_footer = '';
    $GVARS       = [
        'allow_creator_vote' => 1,
        'allow_result_view'  => 1,
        'allow_poll_tags'    => 1,
    ];
    $poll_data = get_poll();

    if (empty($poll_data)) {
        return '';
    }

    $member_voted = 0;
    $total_votes  = 0;

    if ($poll_data['user_id']) {
        $member_voted = 1;
    }

    if ($member_voted) {
        $check       = 1;
        $poll_footer = 'You have already voted';
    }

    if (($poll_data['starter_id'] == $CURUSER['id']) && (1 != $GVARS['allow_creator_vote'])) {
        $check       = 1;
        $poll_footer = 'You created this poll and are not allowed to vote';
    }

    if (1 == $GVARS['allow_result_view']) {
        if (isset($_GET['mode']) && 'show' == $_GET['mode']) {
            $check       = 1;
            $poll_footer = '';
        }
    }
    if (1 == $check) {
        $htmlout      = poll_header($poll_data['pid'], htmlsafechars($poll_data['poll_question'], ENT_QUOTES));
        $poll_answers = unserialize(stripslashes($poll_data['choices']));
        reset($poll_answers);
        foreach ($poll_answers as $id => $data) {
            //subtitle question
            $question    = htmlsafechars($data['question'], ENT_QUOTES);
            $choice_html = '';
            $tv_poll     = 0;
            //get total votes for each choice
            foreach ($poll_answers[$id]['votes'] as $number) {
                $tv_poll += intval($number);
            }
            // Get the choises from the unserialised array
            foreach ($data['choice'] as $choice_id => $text) {
                $choice = htmlsafechars($text, ENT_QUOTES);
                $votes  = intval($data['votes'][$choice_id]);
                if (strlen($choice) < 1) {
                    continue;
                }
                if ($GVARS['allow_poll_tags']) {
                    $choice = preg_replace("/\[url=([^()<>\s]+?)\]((\s|.)+?)\[\/url\]/i", '<a href="\\1">\\2</a>', $choice);
                }
                $percent = 0 == $votes ? 0 : $votes / $tv_poll * 100;
                $percent = sprintf('%.2f', $percent);
                $width   = $percent > 0 ? intval($percent * 2) : 0;
                $choice_html .= poll_show_rendered_choice($choice_id, $votes, $id, $choice, $percent, $width);
            }
            $htmlout .= poll_show_rendered_question($question, $choice_html);
        }
        $htmlout .= show_total_votes($tv_poll);
    } elseif (2 == $check) {
        // only for guests when view before vote is off
        $htmlout = poll_header($poll_data['pid'], htmlsafechars($poll_data['poll_question'], ENT_QUOTES));
        $htmlout .= poll_show_no_guest_view();
        $htmlout .= show_total_votes($total_votes);
    } else {
        $poll_answers = unserialize(stripslashes($poll_data['choices']));
        reset($poll_answers);
        //output poll form
        $htmlout = poll_header($poll_data['pid'], htmlsafechars($poll_data['poll_question'], ENT_QUOTES));
        foreach ($poll_answers as $id => $data) {
            // get the question again!
            $question    = htmlsafechars($data['question'], ENT_QUOTES);
            $choice_html = '';
            // get choices for this question
            foreach ($data['choice'] as $choice_id => $text) {
                $choice = htmlsafechars($text, ENT_QUOTES);
                $votes  = intval($data['votes'][$choice_id]);
                if (strlen($choice) < 1) {
                    continue;
                }
                //do we wanna allow URL's and if so convert them
                if ($GVARS['allow_poll_tags']) {
                    $choice = $s = preg_replace("/\[url=([^()<>\s]+?)\]((\s|.)+?)\[\/url\]/i", '<a href="\\1">\\2</a>', $choice);
                }
                if (isset($data['multi']) && $data['multi'] == 1) {
                    $choice_html .= poll_show_form_choice_multi($choice_id, $votes, $id, $choice);
                } else {
                    $choice_html .= poll_show_form_choice($choice_id, $votes, $id, $choice);
                }
            }
            $choice_html = "<table>{$choice_html}</table>";
            $htmlout .= poll_show_form_question($id, $question, $choice_html);
        }
        $htmlout .= show_total_votes($total_votes);
    }
    $htmlout .= poll_footer();
    if ('' != $poll_footer) {
        $htmlout = str_replace('<!--VOTE-->', $poll_footer, $htmlout);
    } else {
        if (1 == $GVARS['allow_result_view']) {
            if (isset($_GET['mode']) && 'show' == $_GET['mode']) {
                $htmlout = str_replace('<!--SHOW-->', button_show_voteable(), $htmlout);
            } else {
                $htmlout = str_replace('<!--SHOW-->', button_show_results(), $htmlout);
                $htmlout = str_replace('<!--VOTE-->', button_vote(), $htmlout);
            }
        } else {
            //this section not for reviewing votes!
            $htmlout = str_replace('<!--VOTE-->', button_vote(), $htmlout);
            $htmlout = str_replace('<!--SHOW-->', button_null_vote(), $htmlout);
        }
    }

    return $htmlout;
}

/**
 * @param string $pid
 * @param string $poll_q
 *
 * @return string
 */
function poll_header($pid = '', $poll_q = '')
{
    global $site_config;
    $HTMLOUT = '';
    $HTMLOUT .= "<script>
    /*<![CDATA[*/
    function go_gadget_show()
    {
      window.location = \"{$site_config['baseurl']}/index.php?pollid={$pid}&mode=show&st=main\";
    }
    function go_gadget_vote()
    {
      window.location = \"{$site_config['baseurl']}/index.php?pollid={$pid}&st=main\";
    }
    /*]]>*/
    </script>
    <a id='poll-hash'></a>
    <fieldset id='poll' class='header'>
        <legend class='flipper has-text-primary has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$poll_q}</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00'>
                <form action='{$site_config['baseurl']}/polls_take_vote.php?pollid={$pid}&amp;st=main&amp;addpoll=1' method='post'>";

    return $HTMLOUT;
}

/**
 * @return string
 */
function poll_footer()
{
    return '
                    <div class="has-text-centered"><!--VOTE--><!--SHOW--></div>
                    <div class="has-text-centered"><!-- no content --></div>
                </form>
            </div>
        </div>
    </fieldset>';
}

/**
 * @param string $choice_id
 * @param string $votes
 * @param string $id
 * @param string $answer
 * @param string $percentage
 * @param string $width
 *
 * @return string
 */
function poll_show_rendered_choice($choice_id = '', $votes = '', $id = '', $answer = '', $percentage = '', $width = '')
{
    global $site_config;

    return "
        <div class='bottom20 bg-02 round10 padding10'>
            <div class='bg-02 round10 padding10'>
                $answer
            </div>
            <div class='has-text-centered top10'>
                <img src='{$site_config['pic_baseurl']}polls/bar.gif' style='width: {$width}px; height: 11px;' align='middle' alt='' />
                [$percentage%]
                <h1>Total Votes: $votes</h1>
            </div>
        </div>";
}

/**
 * @param string $id
 * @param string $question
 * @param string $choice_html
 *
 * @return string
 */
function poll_show_rendered_question($question = '', $choice_html = '')
{
    return "
        <div class='has-text-centered'>
            <div class='round10'>
                <h1>
                    $question
                </h1>
            </div>
            $choice_html
        </div>";
}

/**
 * @param string $total_votes
 *
 * @return string
 */
function show_total_votes($total_votes = '')
{
    return "
        <div class='has-text-centered top10'>
            <b>Total Votes: $total_votes</b>
        </div>";
}

/**
 * @param string $choice_id
 * @param string $votes
 * @param string $id
 * @param string $answer
 *
 * @return string
 */
function poll_show_form_choice_multi($choice_id = '', $votes = '', $id = '', $answer = '')
{
    return "
    <tr>
        <td colspan='3'><input type='checkbox' name='choice_{$id}_{$choice_id}' value='1'  />&#160;<b>$answer</b></td>
    </tr>";
}

/**
 * @param string $choice_id
 * @param string $votes
 * @param string $id
 * @param string $answer
 *
 * @return string
 */
function poll_show_form_choice($choice_id = '', $votes = '', $id = '', $answer = '')
{
    return "
        <div class='padding10'>
            <input type='radio' name='choice[{$id}]' value='$choice_id' class='right10' /> $answer
        </div>";
}

/**
 * @param string $id
 * @param string $question
 * @param string $choice_html
 *
 * @return string
 */
function poll_show_form_question($id = '', $question = '', $choice_html = '')
{
    return "
    <div class='bg-02 round5 padding10'>
        <div>
            <div class='has-text-white size_6 padding10'>
                {$question}
            </div>
        </div>
        $choice_html
    </div>";
}

/**
 * @return string
 */
function button_show_voteable()
{
    return "<input class='button is-small tooltipper margin10' type='button' name='viewresult' value='Show Votes'  title='Goto poll voting' onclick=\"go_gadget_vote()\" />";
}

/**
 * @return string
 */
function button_show_results()
{
    return "<input class='button is-small tooltipper margin10' type='button' value='Results' title='Show all poll results' onclick=\"go_gadget_show()\" />";
}

/**
 * @return string
 */
function button_vote()
{
    return "<input class='button is-small tooltipper margin10' type='submit' name='submit' value='Vote' title='Cast Your Vote' />";
}

/**
 * @return string
 */
function button_null_vote()
{
    return "<input class='button is-small tooltipper margin10' type='submit' name='nullvote' value='View Results (Null Vote)' title='View results, but forfeit your vote in this poll' />";
}
