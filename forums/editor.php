<?php

declare(strict_types = 1);

$subscribe = empty($subscribe) ? 'no' : $subscribe;
$bb_code = empty($bb_code) || $bb_code === 'yes' ? 'yes' : 'no';
$show_edited_by = empty($show_edited_by) ? 'yes' : 'no';
$can_edit = !empty($can_edit) ? $can_edit : true;
$edit = (preg_match('/edit_post/', $_SERVER['QUERY_STRING']) ? "
	<tr>
		<td>{$lang['fe_reason']}</td>
		<td>
			<input type='text' maxlength='60' name='edit_reason' value='" . trim(strip_tags($edit_reason)) . "' class='w-100' placeholder='Optional'>
		</td>
	</tr>" . ($CURUSER['class'] >= $site_config['allowed']['show_edited_by'] || $CURUSER['id'] === $arr_post['id'] ? "
	<tr>
		<td>Edited By</td>
		<td>
			<input type='radio' name='show_edited_by' value='yes'" . ($show_edited_by === 'yes' ? ' checked' : '') . "> Yes
			<input type='radio' name='show_edited_by' value='no'" . ($show_edited_by === 'no' ? ' checked' : '') . '> No
		</td>
	</tr>' : '') : '');

$HTMLOUT .= main_table('
        <tr>
            <td class="w15">
                <span>' . $lang['fe_icon'] . '</span>
            </td>
            <td>
                <div class="level-center">
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/smile1.gif" alt="' . $lang['fe_smile'] . '" title="' . $lang['fe_smile'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="smile1"' . ($icon === 'smile1' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/grin.gif" alt="' . $lang['fe_smilee_grin'] . '" title="' . $lang['fe_smilee_grin'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="grin"' . ($icon === 'grin' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/tongue.gif" alt="' . $lang['fe_smilee_tongue'] . '" title="' . $lang['fe_smilee_tongue'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="tongue"' . ($icon === 'tongue' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/cry.gif" alt="' . $lang['fe_smilee_cry'] . '" title="' . $lang['fe_smilee_cry'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="cry"' . ($icon === 'cry' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/wink.gif" alt="' . $lang['fe_smilee_wink'] . '" title="' . $lang['fe_smilee_wink'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="wink"' . ($icon === 'wink' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/rolleyes.gif" alt="' . $lang['fe_smilee_roll_eyes'] . '" title="' . $lang['fe_smilee_roll_eyes'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="rolleyes"' . ($icon === 'rolleyes' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/blink.gif" alt="' . $lang['fe_smilee_blink'] . '" title="' . $lang['fe_smilee_blink'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="blink"' . ($icon === 'blink' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/bow.gif" alt="' . $lang['fe_smilee_bow'] . '" title="' . $lang['fe_smilee_bow'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="bow"' . ($icon === 'bow' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/clap2.gif" alt="' . $lang['fe_smilee_clap'] . '" title="' . $lang['fe_smilee_clap'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="clap2"' . ($icon === 'clap2' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/hmmm.gif" alt="' . $lang['fe_smilee_hmm'] . '" title="' . $lang['fe_smilee_hmm'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="hmmm"' . ($icon === 'hmmm' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/devil.gif" alt="' . $lang['fe_smilee_devil'] . '" title="' . $lang['fe_smilee_devil'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="devil"' . ($icon === 'devil' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/angry.gif" alt="' . $lang['fe_smilee_angry'] . '" title="' . $lang['fe_smilee_angry'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="angry"' . ($icon === 'angry' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt="' . $lang['fe_smilee_shit'] . '" title="' . $lang['fe_smilee_shit'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="shit"' . ($icon === 'shit' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/sick.gif" alt="' . $lang['fe_smilee_sick'] . '" title="' . $lang['fe_smilee_sick'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="sick"' . ($icon === 'sick' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/tease.gif" alt="' . $lang['fe_smilee_tease'] . '" title="' . $lang['fe_smilee_tease'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="tease"' . ($icon === 'tease' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/love.gif" alt="' . $lang['fe_smilee_love'] . '" title="' . $lang['fe_smilee_love'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="love"' . ($icon === 'love' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/ohmy.gif" alt="' . $lang['fe_smilee_oh_my'] . '" title="' . $lang['fe_smilee_oh_my'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="ohmy"' . ($icon === 'ohmy' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/yikes.gif" alt="' . $lang['fe_smilee_yikes'] . '" title="' . $lang['fe_smilee_yikes'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="yikes"' . ($icon === 'yikes' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/spider.gif" alt="' . $lang['fe_smilee_spider'] . '" title="' . $lang['fe_smilee_spider'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="spider"' . ($icon === 'spider' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/wall.gif" alt="' . $lang['fe_smilee_wall'] . '" title="' . $lang['fe_smilee_wall'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="wall"' . ($icon === 'wall' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/idea.gif" alt="' . $lang['fe_smilee_idea'] . '" title="' . $lang['fe_smilee_idea'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="idea"' . ($icon === 'idea' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/question.gif" alt="' . $lang['fe_smilee_question'] . '" title="' . $lang['fe_smilee_question'] . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="question"' . ($icon === 'question' ? ' checked' : '') . '>
                    </span>
                </div>
            </td>
        </tr>
        <tr>' . ($can_edit ? '
            <td>
                <span>' . $lang['fe_name'] . '</span>
            </td>
            <td>
                <input type="text" maxlength="120" name="topic_name" value="' . trim(strip_tags($topic_name)) . '" class="w-100"    placeholder="required" required>
            </td>
        </tr>' : '') . '
        <tr>
            <td>
                <span>' . $lang['fe_desc'] . '</span>
            </td>
            <td>
                <input type="text" maxlength="120" name="topic_desc" value="' . trim(strip_tags($topic_desc)) . '" class="w-100" placeholder="optional">
            </td>
        </tr>
        <tr>
            <td>
                <span>' . $lang['fe_title'] . '</span>
            </td>
            <td>
                <input type="text" maxlength="120" name="post_title" value="' . trim(strip_tags($post_title)) . '" class="w-100" placeholder="optional">
            </td>
        </tr>
        <tr>
            <td>
                <span>' . $lang['fe_bbcode'] . '</span>
            </td>
            <td>
                <div>
                    <input type="radio" name="bb_code" value="yes"' . ($bb_code === 'yes' ? ' checked' : '') . '> Allow ' . $lang['fe_bbcode_in_post'] . '
                </div>
                <div>
                    <input type="radio" name="bb_code" value="no"' . ($bb_code === 'no' ? ' checked' : '') . '> No ' . $lang['fe_bbcode_in_post'] . '
                </div>
            </td>
        </tr>' . $edit . '
        <tr>
            <td><span>' . $lang['fe_body'] . '</span></td>
            <td class="is-paddingless">' . BBcode($body) . ' 
				<div class="level-center margin20">
					<span class="level-center">
						<a class="is-link flipper" title="' . $lang['fm_additional_options'] . '" id="staff_tools_open">
							<i class="icon-up-open size_2" aria-hidden="true"></i>' . $lang['fm_additional_options'] . '
						</a>
					</span>
				</div>' . $more_options . '
			</td>
        </tr>
        <tr>
            <td>
                Anonymous
            </td>
            <td>
                <div class="level-left">
                    <span class="level-center">
                        <input type="checkbox" name="anonymous" value="yes" class="right10">
                        ' . $lang['fe_anonymous_topic'] . '
                    </span>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                ' . $lang['fe_subscrib_to_tread'] . '
            </td>
            <td>
                <div class="level-left">
                    <span class="level-center flex-vertical right10">
                        yes
                        <input type="radio" name="subscribe" value="yes"' . ($subscribe === 'yes' ? ' checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        no
                        <input type="radio" name="subscribe" value="no"' . ($subscribe === 'no' ? ' checked' : '') . '>
                    </span>
                </div>
            </td>
        </tr>');
