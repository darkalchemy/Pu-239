<?php

declare(strict_types = 1);

$user = check_user_status();
$subscribe = empty($subscribe) ? 'no' : $subscribe;
$bb_code = empty($bb_code) || $bb_code === 'yes' ? 'yes' : 'no';
$show_edited_by = empty($show_edited_by) ? 'yes' : 'no';
$can_edit = !empty($can_edit) ? $can_edit : true;
$edit = (preg_match('/edit_post/', $_SERVER['QUERY_STRING']) ? '
	<tr>
		<td>' . _('Reason') . "</td>
		<td>
			<input type='text' maxlength='60' name='edit_reason' value='" . trim(strip_tags($edit_reason)) . "' class='w-100' placeholder='Optional'>
		</td>
	</tr>" . (has_access($user['class'], $site_config['allowed']['show_edited_by'], 'coder') || $user['id'] === $arr_post['id'] ? "
	<tr>
		<td>Edited By</td>
		<td>
			<input type='radio' name='show_edited_by' value='yes' " . ($show_edited_by === 'yes' ? 'checked' : '') . '>' . _('Yes') . "
			<input type='radio' name='show_edited_by' value='no' " . ($show_edited_by === 'no' ? 'checked' : '') . '>' . _('No') . '
		</td>
	</tr>' : '') : '');

$HTMLOUT .= main_table('
        <tr>
            <td class="w15">
                <span>' . _('Icon') . '</span>
            </td>
            <td>
                <div class="level-center">
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/smile1.gif" alt="' . _('Smile') . '" title="' . _('Smile') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="smile1" ' . ($icon === 'smile1' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/grin.gif" alt="' . _('Grin') . '" title="' . _('Grin') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="grin" ' . ($icon === 'grin' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/tongue.gif" alt="' . _('Tongue') . '" title="' . _('Tongue') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="tongue" ' . ($icon === 'tongue' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/cry.gif" alt="' . _('Cry') . '" title="' . _('Cry') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="cry" ' . ($icon === 'cry' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/wink.gif" alt="' . _('Wink') . '" title="' . _('Wink') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="wink" ' . ($icon === 'wink' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/rolleyes.gif" alt="' . _('Roll eyes') . '" title="' . _('Roll eyes') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="rolleyes" ' . ($icon === 'rolleyes' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/blink.gif" alt="' . _('Blink') . '" title="' . _('Blink') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="blink" ' . ($icon === 'blink' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/bow.gif" alt="' . _('Bow') . '" title="' . _('Bow') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="bow" ' . ($icon === 'bow' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/clap2.gif" alt="' . _('Clap') . '" title="' . _('Clap') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="clap2" ' . ($icon === 'clap2' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/hmmm.gif" alt="' . _('Hmm') . '" title="' . _('Hmm') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="hmmm" ' . ($icon === 'hmmm' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/devil.gif" alt="' . _('Devil') . '" title="' . _('Devil') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="devil" ' . ($icon === 'devil' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/angry.gif" alt="' . _('Angry') . '" title="' . _('Angry') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="angry" ' . ($icon === 'angry' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/shit.gif" alt="' . _('Shit') . '" title="' . _('Shit') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="shit" ' . ($icon === 'shit' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/sick.gif" alt="' . _('Sick') . '" title="' . _('Sick') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="sick" ' . ($icon === 'sick' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/tease.gif" alt="' . _('Tease') . '" title="' . _('Tease') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="tease" ' . ($icon === 'tease' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/love.gif" alt="' . _('Love') . '" title="' . _('Love') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="love" ' . ($icon === 'love' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/ohmy.gif" alt="' . _('Oh my') . '" title="' . _('Oh my') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="ohmy" ' . ($icon === 'ohmy' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/yikes.gif" alt="' . _('Yikes') . '" title="' . _('Yikes') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="yikes" ' . ($icon === 'yikes' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/spider.gif" alt="' . _('Spider') . '" title="' . _('Spider') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="spider" ' . ($icon === 'spider' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/wall.gif" alt="' . _('Wall') . '" title="' . _('Wall') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="wall" ' . ($icon === 'wall' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/idea.gif" alt="' . _('Idea') . '" title="' . _('Idea') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="idea" ' . ($icon === 'idea' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        <img src="' . $site_config['paths']['images_baseurl'] . 'smilies/question.gif" alt="' . _('Question') . '" title="' . _('Question') . '" class="tooltipper icon bottom10">
                        <input type="radio" name="icon" value="question" ' . ($icon === 'question' ? 'checked' : '') . '>
                    </span>
                </div>
            </td>
        </tr>
        <tr>' . ($can_edit ? '
            <td>
                <span>' . _('Name') . '</span>
            </td>
            <td>
                <input type="text" maxlength="120" name="topic_name" value="' . format_comment($topic_name) . '" class="w-100" placeholder="required" required>
            </td>
        </tr>' : '') . '
        <tr>
            <td>
                <span>' . _('Desc') . '</span>
            </td>
            <td>
                <input type="text" maxlength="120" name="topic_desc" value="' . format_comment($topic_desc) . '" class="w-100" placeholder="optional">
            </td>
        </tr>
        <tr>
            <td>
                <span>' . _('Title') . '</span>
            </td>
            <td>
                <input type="text" maxlength="120" name="post_title" value="' . format_comment($post_title) . '" class="w-100" placeholder="optional">
            </td>
        </tr>
        <tr>
            <td>
                <span>' . _('BBcode') . '</span>
            </td>
            <td>
                <div>
                    <input type="radio" name="bb_code" value="yes" ' . ($bb_code === 'yes' ? 'checked' : '') . '>' . _('Allow BBcode in post') . '
                </div>
                <div>
                    <input type="radio" name="bb_code" value="no" ' . ($bb_code === 'no' ? 'checked' : '') . '>' . _('No BBcode allowed in post') . '
                </div>
            </td>
        </tr>' . $edit . '
        <tr>
            <td><span>' . _('Body') . '</span></td>
            <td class="is-paddingless">' . BBcode($body) . ' 
				<div class="level-center margin20">
					<span class="level-center">
						<a class="is-link flipper" title="' . _('Additional Options') . '" id="staff_tools_open">
							<i class="icon-up-open size_2" aria-hidden="true"></i>' . _('Additional Options') . '
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
                        <input type="checkbox" name="anonymous" value="1" class="right10">
                        ' . _('Anonymous topic') . '
                    </span>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                ' . _('Subscribe to this thread') . '
            </td>
            <td>
                <div class="level-left">
                    <span class="level-center flex-vertical right10">
                        yes
                        <input type="radio" name="subscribe" value="yes" ' . ($subscribe === 'yes' ? 'checked' : '') . '>
                    </span>
                    <span class="level-center flex-vertical margin10">
                        no
                        <input type="radio" name="subscribe" value="no" ' . ($subscribe === 'no' ? 'checked' : '') . '>
                    </span>
                </div>
            </td>
        </tr>');
