<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

	/* This will be moved to its own template... eventually */
function template_admin_home()
{
	global $txt, $context;

	echo '
	<script type="text/javascript">
$(document).ready(function () {
	$(\'#Breeze_rss\').rssfeed(\'',$context['breeze']['rss_url'],'\', {
		limit: 5
	});
});
</script>
	<div class="breeze_rss_box">
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<div class="content">
					<div id="Breeze_rss"></div>
				</div>
			</div>
		</div>
		<span class="lowerframe">
			<span></span>
		</span>
	</div>
	<div class="breeze_admin_info">
	';

	foreach($context['breeze']['versions'] as $version)
		echo $version;

	echo'<br />I dont know what else I should put in here...</div>
	<div class="clear"></div>';
}


/* General wall... */
	/* This will be moved to its own template... eventually */
function template_general_wall()
{
	global $txt;

	echo '
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<div class="content">
					',$txt['breeze_admin_settings_donate_text'],'
				</div>
			</div>
		</div>
		<span class="lowerframe">
			<span></span>
		</span><br />';
}

/* User's wall. */
function template_user_wall()
{
	global $txt, $context, $settings, $scripturl, $user_info, $memberContext;

	echo '<div id="profileview" class="flow_auto">
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft"><img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" class="icon" />', $txt['summary'], '</span>
				</h3>
			</div>
				<div id="basicinfo">
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content flow_auto">
				<div class="username"><h4>', $context['member']['name'], ' <span class="position">', (!empty($context['member']['group']) ? $context['member']['group'] : $context['member']['post_group']), '</span></h4></div>
				', $context['member']['avatar']['image'], '
				<ul class="reset">';

	// What about if we allow email only via the forum??
	if ($context['member']['show_email'] === 'yes' || $context['member']['show_email'] === 'no_through_forum' || $context['member']['show_email'] === 'yes_permission_override')
		echo '
					<li><a href="', $scripturl, '?action=emailuser;sa=email;uid=', $context['member']['id'], '" title="', $context['member']['show_email'] == 'yes' || $context['member']['show_email'] == 'yes_permission_override' ? $context['member']['email'] : '', '" rel="nofollow"><img src="', $settings['images_url'], '/email_sm.gif" alt="', $txt['email'], '" /></a></li>';

	// Don't show an icon if they haven't specified a website.
	if ($context['member']['website']['url'] !== '' && !isset($context['disabled_fields']['website']))
		echo '
					<li><a href="', $context['member']['website']['url'], '" title="' . $context['member']['website']['title'] . '" target="_blank" class="new_win">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $context['member']['website']['title'] . '" />' : $txt['www']), '</a></li>';

	// Are there any custom profile fields for the summary?
	if (!empty($context['custom_fields']))
	{
		foreach ($context['custom_fields'] as $field)
			if (($field['placement'] == 1 || empty($field['output_html'])) && !empty($field['value']))
				echo '
					<li class="custom_field">', $field['output_html'], '</li>';
	}

	echo '
				', !isset($context['disabled_fields']['icq']) && !empty($context['member']['icq']['link']) ? '<li>' . $context['member']['icq']['link'] . '</li>' : '', '
				', !isset($context['disabled_fields']['msn']) && !empty($context['member']['msn']['link']) ? '<li>' . $context['member']['msn']['link'] . '</li>' : '', '
				', !isset($context['disabled_fields']['aim']) && !empty($context['member']['aim']['link']) ? '<li>' . $context['member']['aim']['link'] . '</li>' : '', '
				', !isset($context['disabled_fields']['yim']) && !empty($context['member']['yim']['link']) ? '<li>' . $context['member']['yim']['link'] . '</li>' : '', '
			</ul>
			<span id="userstatus">', $context['can_send_pm'] ? '<a href="' . $context['member']['online']['href'] . '" title="' . $context['member']['online']['label'] . '" rel="nofollow">' : '', $settings['use_image_buttons'] ? '<img src="' . $context['member']['online']['image_href'] . '" alt="' . $context['member']['online']['text'] . '" align="middle" />' : $context['member']['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $context['member']['online']['text'] . '</span>' : '';

	// Can they add this member as a buddy?
	if (!empty($context['can_have_buddy']) && !$context['user']['is_owner'])
		echo '
				<br /><a href="', $scripturl, '?action=buddy;u=', $context['id_member'], ';', $context['session_var'], '=', $context['session_id'], '">[', $txt['buddy_' . ($context['member']['is_buddy'] ? 'remove' : 'add')], ']</a>';

	echo '
				</span>';

	echo '
				<p id="infolinks">';

	if (!$context['user']['is_owner'] && $context['can_send_pm'])
		echo '
					<a href="', $scripturl, '?action=pm;sa=send;u=', $context['id_member'], '">', $txt['profile_sendpm_short'], '</a><br />';
	echo '
					<a href="', $scripturl, '?action=profile;area=showposts;u=', $context['id_member'], '">', $txt['showPosts'], '</a><br />
					<a href="', $scripturl, '?action=profile;area=statistics;u=', $context['id_member'], '">', $txt['statPanel'], '</a>
				</p>';

	echo '
			</div>
			<span class="botslice"><span></span></span>
		</div>';

		/* Modules */
		echo'<div class="breeze_modules">';

		$counter = 0;

		foreach($context['Breeze']['Modules'] as $m)
		{
			$counter++;

			if ($counter % 2 == 0)
				$class_id = '';

			else
				$class_id = '2';

			echo '<div class="cat_bar">
					<h3 class="catbg">
						<span class="ie6_header floatleft">
							',$m['title'],'
						</span>
					</h3>
				</div>';

			echo '<div class="windowbg',$class_id,'">
					<span class="topslice"><span></span></span>
					<div class="content">
						',$m['data'],'
					</div>
					<span class="botslice"><span></span></span>
				</div>';

		}

		/* Modules end */
		echo '</div></div>';

	/* End of right side */

	/* Left side */
	echo '<div id="detailedinfo">
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">';

			/* Main content */
				/* This is the status box,  O RLY? */
			echo '<div class="breeze_user_inner">
					<div class="breeze_user_statusbox">
						<form method="post" action="', $scripturl, '?action=breezeajax;sa=post" id="status" name="form">
							<textarea cols="40" rows="5" name="content" id="content" ></textarea>
							<input type="hidden" value="',$context['member']['id'],'" name="owner_id" id="owner_id" />
							<input type="hidden" value="',$user_info['id'],'" name="poster_id" id="poster_id" /><br />
							<input type="submit" value="Update" name="submit" class="status_button"/>
						</form>
					</div>
				</div>';
		echo'</div>
			<span class="botslice"><span></span></span>
		</div>';
		/* End of the status textarea */


	/* New ajax status here DO NOT MODIFY THIS UNLESS YOU KNOW WHAT YOU'RE DOING*/
	echo '<div id="breeze_load_image"></div><div id="breeze_display_status"></div>';

	/* Status and comments */
	foreach ($context['member']['status'] as $k => $status)
	{
		echo '<div class="windowbg" id ="status_id_',$status['id'],'">
			<span class="topslice"><span></span></span>
				<div class="breeze_user_inner">
					<div class="breeze_user_status_avatar">
						',$status['breeze_user_info'],'
					</div>
					<div class="breeze_user_status_comment">
						',$status['body'],'
						<div class="breeze_options"><span class="time_elapsed">',$status['time'],'</span>  <a href="javascript:void(0)" id="',$status['id'],'" class="breeze_delete_status">Delete</a> </div>
						<hr />
						<div id="comment_flash_',$status['id'],'"></div>';

						/* Print out the comments */
						foreach($status['comments'] as $comment)
							echo'<div class="description" id ="comment_id_',$comment['id'],'">
									<div class="breeze_user_inner">
										<div class="breeze_user_status_avatar">
											',$comment['comment_user_info'],'<br />
										</div>
										<div class="breeze_user_status_comment">
											',$comment['body'],'
											<div class="breeze_options">
												<span class="time_elapsed">',$comment['time'],'</span> | <a href="javascript:void(0)" id="',$comment['id'],'" class="breeze_delete_comment">Delete</a>
												<span class="breeze_like_status_id_',$comment['id'],'" id="',$status['id'],'"></span>
												<span class="breeze_like_comment_id_',$comment['id'],'" id="',$comment['id'],'"></span>
												<span class="breeze_like_userwholiked_id_',$comment['id'],'" id="',$comment['poster_comment_id'],'"></span>
												<span class="breeze_like_profile_id_',$comment['id'],'" id="',$context['member']['id'],'"></span>
											</div>
										</div>
										<div class="clear"></div>
									</div>
								</div>';

						echo'<div id="comment_loadplace_',$status['id'],'"></div>

							<form action="', $scripturl, '?action=breezeajax;sa=postcomment" method="post" name="formID_',$status['id'],'" id="formID_',$status['id'],'">
								<textarea id="textboxcontent_',$status['id'],'" cols="40" rows="2"></textarea>
								<input type="hidden" value="',$status['poster_id'],'" name="status_owner_id',$status['id'],'" id="status_owner_id',$status['id'],'" />
								<input type="hidden" value="',$context['member']['id'],'" name="profile_owner_id',$status['id'],'" id="profile_owner_id',$status['id'],'" />
								<input type="hidden" value="',$status['id'],'" name="status_id',$status['id'],'" id="status_id',$status['id'],'" />
								<input type="hidden" value="',$user_info['id'],'" name="poster_comment_id',$status['id'],'" id="poster_comment_id',$status['id'],'" /><br />
								<input type="submit" value="Comment" class="comment_submit" id="',$status['id'],'" />
							</form>
					</div>
					<div class="clear"></div>
				</div>
			<span class="botslice"><span></span></span>
			</div>';
	}

	/* End of left side */
	echo '</div>
	<div class="clear"></div>
	</div>';
}

/* Boring stuff you will never see... */
function template_admin_donate()
{
	global $txt;

	echo '
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<div class="content">
					',$txt['breeze_admin_settings_donate_text'],'
				</div>
			</div>
		</div>
		<span class="lowerframe">
			<span></span>
		</span><br />';
}