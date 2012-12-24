<?php

/**
 * Breeze_
 *
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0 Beta 2
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica González.
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

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
					something
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
	global $txt, $context, $settings, $scripturl, $user_info;

	loadLanguage(Breeze::$name);

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
							<span id="userstatus">', $context['can_send_pm'] ? '
								<a href="' . $context['member']['online']['href'] . '" title="' . $context['member']['online']['label'] . '" rel="nofollow">' : '', $settings['use_image_buttons'] ? '<img src="' . $context['member']['online']['image_href'] . '" alt="' . $context['member']['online']['text'] . '" align="middle" />' : $context['member']['online']['text'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $context['member']['online']['text'] . '</span>' : '';

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
					<span class="botslice">
						<span></span>
					</span>
				</div>';

		/* Modules */
		echo'<div class="breeze_modules">';

		$counter = 0;

		if (!empty($context['Breeze']['Modules']))
			foreach($context['Breeze']['Modules'] as $m)
				if (!empty($m))
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
				<span class="topslice">
					<span></span>
				</span>
			<div class="content">';

			/* Main content */

			/* This is the status box,  O RLY? */
			if ($context['Breeze']['visitor']['post_status'])
				echo '<div class="breeze_user_inner">
						<div class="breeze_user_statusbox">
							<form method="post" action="', $scripturl, '?action=breezeajax;sa=post" id="status" name="form_status" class="form_status">
								<textarea cols="40" rows="5" name="content" id="content" ></textarea>
								<input type="hidden" value="',$context['member']['id'],'" name="owner_id" id="owner_id" />
								<input type="hidden" value="',$user_info['id'],'" name="poster_id" id="poster_id" /><br />
								<input type="submit" value="', $txt['post'] ,'" name="submit" class="status_button"/>
							</form>
						</div>
					</div>';

		echo'</div>
			<span class="botslice">
				<span></span>
			</span>
		</div>';
		/* End of the status textarea */


	/* New ajax status here DO NOT MODIFY THIS UNLESS YOU KNOW WHAT YOU'RE DOING and even if you do, DON'T MODIFY THIS */
	echo '<span id="breeze_load_image" style="margin:auto; text-align:center;"></span>
	<ul class="breeze_status" id="breeze_display_status">';

	/* Status and comments */
	if (!empty($context['member']['status']))
		foreach ($context['member']['status'] as $status)
		{
			echo '<li class="windowbg" id ="status_id_', $status['id'] ,'">
				<span class="topslice"><span></span></span>
					<div class="breeze_user_inner">
						<div class="breeze_user_status_avatar">
							',$context['Breeze']['user_info'][$status['poster_id']]['facebox'],'
						</div>
						<div class="breeze_user_status_comment">
							',$status['body'],'
							<div class="breeze_options"><span class="time_elapsed">', $status['time'] ,' </span>';

							/* Delete link */
							if ($context['Breeze']['visitor']['delete_status_comments'])
								echo '| <a href="javascript:void(0)" id="', $status['id'] ,'" class="breeze_delete_status">', $txt['Breeze_general_delete'] ,'</a> </div>';

							echo '<hr />
							<div id="comment_flash_', $status['id'] ,'"></div>';
						echo '<ul class="breeze_comments_list" id="comment_loadplace_', $status['id'] ,'">';

							/* Print out the comments */
							if (!empty($status['comments']))
								foreach($status['comments'] as $comment)
								{
									echo '<li class="windowbg2" id ="comment_id_', $comment['id'] ,'">
												<div class="breeze_user_comment_avatar">
													',$context['Breeze']['user_info'][$comment['poster_id']]['facebox'],'<br />
												</div>
												<div class="breeze_user_comment_comment">
													',$comment['body'],'
													<div class="breeze_options">
														<span class="time_elapsed">', $comment['time'] ,'</span>';

									/* Delete comment */
									if ($context['Breeze']['visitor']['delete_status_comments'])
										echo '| <a href="javascript:void(0)" id="', $comment['id'] ,'" class="breeze_delete_comment">', $txt['Breeze_general_delete'] ,'</a>';

									echo '
													</div>
												</div>
												<div class="clear"></div>
											</li>';
								}

							/* Display the new comments */
							echo '<li id="breeze_load_image_comment_', $status['id'] ,'" style="margin:auto; text-align:center;"></li>';

							echo '</ul>';

								/* Post a new comment */
								if ($context['Breeze']['visitor']['post_comment'])
									echo '<div>
									<form action="', $scripturl , '?action=breezeajax;sa=postcomment" method="post" name="formID_', $status['id'] ,'" id="formID_', $status['id'] ,'">
										<textarea id="textboxcontent_', $status['id'] ,'" cols="40" rows="2"></textarea>
										<input type="hidden" value="',$status['poster_id'],'" name="status_owner_id', $status['id'] ,'" id="status_owner_id', $status['id'] ,'" />
										<input type="hidden" value="',$context['member']['id'],'" name="profile_owner_id', $status['id'] ,'" id="profile_owner_id', $status['id'] ,'" />
										<input type="hidden" value="', $status['id'] ,'" name="status_id', $status['id'] ,'" id="status_id', $status['id'] ,'" />
										<input type="hidden" value="',$user_info['id'],'" name="poster_comment_id', $status['id'] ,'" id="poster_comment_id', $status['id'] ,'" /><br />
										<input type="submit" value="', $txt['post'] ,'" class="comment_submit" id="', $status['id'] ,'" />
									</form>
								</div>';

						echo '
						</div>
						<div class="clear"></div>
					</div>
				<span class="botslice">
					<span></span>
				</span>
				</li>';
		}

	/* End of list */
	echo '</ul>';

	/* Pagination panel */
	if (!empty($context['Breeze']['pagination']['panel']))
		echo $context['Breeze']['pagination']['panel'];

	/* End of left side */
	echo '</div>
		<div class="clear"></div>
	</div>';

	/* Don't forget to print the users data */
	if (!empty($context['Breeze']['user_info']))
		foreach ($context['Breeze']['user_info'] as $userData)
			if (!empty($userData['data']))
				echo $userData['data'];
}

function template_user_notifications()
{
	global $context, $txt;

	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $context['page_title'] ,'</h3>
		</div>
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">
				stuff here!
			</div>
			<span class="botslice">
				<span></span>
			</span>
		</div>';
}

function template_singleStatus()
{
	global $context, $txt, $user_info, $scripturl;

	echo '
			<div class="windowbg" id ="status_id_', $context['Breeze']['single']['id'] ,'">
				<span class="topslice">
					<span></span>
				</span>
				<div class="breeze_user_inner">
					<div class="breeze_user_status_avatar">
						', $context['Breeze']['user_info'][$context['Breeze']['single']['poster_id']]['facebox'] ,'
					</div>
					<div class="breeze_user_status_comment">
						', $context['Breeze']['single']['body'] ,'
						<div class="breeze_options">
							<span class="time_elapsed">', $context['Breeze']['single']['time'] ,' </span>';

					/* Delete link */
					if ($context['Breeze']['visitor']['delete_status_comments'])
						echo '| <a href="javascript:void(0)" id="', $context['Breeze']['single']['id'] ,'" class="breeze_delete_status">', $txt['Breeze_general_delete'] ,'</a>';

					echo '
						</div>
						<hr />
						<div id="comment_flash_', $context['Breeze']['single']['id'] ,'"></div>';
				echo '
						<ul class="breeze_comments_list" id="comment_loadplace_', $context['Breeze']['single']['id'] ,'">';

					/* Print out the comments */
					if (!empty($context['Breeze']['single']['comments']))
						foreach($context['Breeze']['single']['comments'] as $comment)
						{
							echo '<li class="windowbg2" id ="comment_id_', $comment['id'] ,'">
										<div class="breeze_user_comment_avatar">
											',$context['Breeze']['user_info'][$comment['poster_id']]['facebox'],'<br />
										</div>
										<div class="breeze_user_comment_comment">
											',$comment['body'],'
											<div class="breeze_options">
												<span class="time_elapsed">', $comment['time'] ,'</span>';

							/* Delete comment */
							if ($context['Breeze']['visitor']['delete_status_comments'])
								echo '| <a href="javascript:void(0)" id="', $comment['id'] ,'" class="breeze_delete_comment">', $txt['Breeze_general_delete'] ,'</a>';

							echo '
											</div>
										</div>
										<div class="clear"></div>
									</li>';
						}

						/* Display the new comments */
						echo '<li id="breeze_load_image_comment_', $context['Breeze']['single']['id'] ,'" style="margin:auto; text-align:center;"></li>';

						echo '</ul>';

							/* Post a new comment */
							if ($context['Breeze']['visitor']['post_comment'])
								echo '<div>
								<form action="', $scripturl , '?action=breezeajax;sa=postcomment" method="post" name="formID_', $context['Breeze']['single']['id'] ,'" id="formID_', $context['Breeze']['single']['id'] ,'">
									<textarea id="textboxcontent_', $context['Breeze']['single']['id'] ,'" cols="40" rows="2"></textarea>
									<input type="hidden" value="',$context['Breeze']['single']['poster_id'],'" name="status_owner_id', $context['Breeze']['single']['id'] ,'" id="status_owner_id', $context['Breeze']['single']['id'] ,'" />
									<input type="hidden" value="', $context['Breeze']['single']['owner_id'] ,'" name="profile_owner_id', $context['Breeze']['single']['id'] ,'" id="profile_owner_id', $context['Breeze']['single']['id'] ,'" />
									<input type="hidden" value="', $context['Breeze']['single']['id'] ,'" name="status_id', $context['Breeze']['single']['id'] ,'" id="status_id', $context['Breeze']['single']['id'] ,'" />
									<input type="hidden" value="',$user_info['id'],'" name="poster_comment_id', $context['Breeze']['single']['id'] ,'" id="poster_comment_id', $context['Breeze']['single']['id'] ,'" /><br />
									<input type="submit" value="', $txt['post'] ,'" class="comment_submit" id="', $context['Breeze']['single']['id'] ,'" />
								</form>
							</div>';

					echo '
					</div>
					<div class="clear"></div>
				</div>
			<span class="botslice">
				<span></span>
			</span>
			</div><br />';

}

function template_member_options()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// The main containing header.
	echo '
		<form action="', $scripturl, '?action=profile;area=breezesettings;save" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator" enctype="multipart/form-data" onsubmit="return checkProfileSubmit();">
			<h3 class="catbg">
				<span class="left"></span>
				<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" class="icon" />
				', $context['page_desc'] , '
			</h3>
			<p class="windowbg description">
				', $context['page_desc'] , '
			</p>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
					<div class="content">';

		/* Print the form */
		echo $context['Breeze']['UserSettings']['Form'];


	// Show the standard "Save Settings" profile button.
	template_profile_save();

	echo '
					</div>
				<span class="botslice"><span></span></span>
			</div>
			<br />
		</form>';
}