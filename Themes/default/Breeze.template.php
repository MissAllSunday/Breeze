<?php

/**
 * Breeze_
 *
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica González
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

// User's wall.
function template_user_wall()
{
	global $txt, $context, $settings, $scripturl, $user_info, $modSettings;

	loadLanguage(Breeze::$name);

	template_server_response();

	// Start of profileview div
	echo '
	<div id="profileview" class="flow_auto">';

	// Left block, user's data and blocks
	echo '
		<div id="Breeze_left_block">';

	echo '
			<div class="cat_bar">
				<h3 class="catbg">
					<span id="author">
						'. $txt['Breeze_tabs_wall'] .'
				</h3>
			</div>';

	// Wall div
	echo '
			<div id="tabs_wall">
				<div class="windowbg2">
					<span class="topslice">
						<span></span>
					</span>
				<div class="content">';

			// This is the status box,  O RLY?
			if (!empty($context['permissions']['post_status']))
				echo '<div class="breeze_user_inner">
						<div class="breeze_user_statusbox">
							<form method="post" action="', $scripturl, '?action=breezeajax;sa=post" id="status" name="form_status" class="form_status">
								<textarea cols="40" rows="5" name="content" id="content" rel="atwhoMention"></textarea>
								<input type="hidden" value="',$context['member']['id'],'" name="owner_id" id="owner_id" />
								<input type="hidden" value="',$user_info['id'],'" name="poster_id" id="poster_id" /><br />
								<input type="submit" value="', $txt['post'] ,'" name="submit" class="status_button"/>
								<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							</form>
						</div>
					</div>';

		echo'
				</div>
				<span class="botslice">
					<span></span>
				</span>
			</div>';
		// End of the status textarea

	// New ajax status here DO NOT MODIFY THIS UNLESS YOU KNOW WHAT YOU'RE DOING and even if you do, DON'T MODIFY THIS
	echo '
		<div id="breeze_load_image"></div>
		<ul class="breeze_status" id="breeze_display_status">';

	// Status and comments
	if (!empty($context['member']['status']))
		foreach ($context['member']['status'] as $status)
		{
			echo '
			<li class="windowbg" id ="status_id_', $status['id'] ,'">
				<span class="topslice"><span></span></span>
					<div class="breeze_user_inner">
						<div class="breeze_user_status_avatar">
							',$context['Breeze']['user_info'][$status['poster_id']]['facebox'],'
						</div>
						<div class="breeze_user_status_comment">
							',$status['body'],'
							<div class="breeze_options"><span class="time_elapsed">', $status['time'] ,' </span>';

							// Delete status
							if (!empty($context['permissions']['delete_status']))
								echo '| <a href="', $scripturl , '?action=breezeajax;sa=delete;bid=', $status['id'] ,';type=status;profile_owner=',$context['member']['id'],'" id="', $status['id'] ,'" class="breeze_delete_status">', $txt['Breeze_general_delete'] ,'</a> </div>';

							echo '<hr />
							<div id="comment_flash_', $status['id'] ,'"></div>';
						echo '
								<ul class="breeze_comments_list" id="comment_loadplace_', $status['id'] ,'">';

							// Print out the comments
							if (!empty($status['comments']))
								foreach($status['comments'] as $comment)
								{
									echo '
									<li class="windowbg2" id ="comment_id_', $comment['id'] ,'">
										<div class="breeze_user_comment_avatar">
												',$context['Breeze']['user_info'][$comment['poster_id']]['facebox'],'<br />
										</div>
										<div class="breeze_user_comment_comment">
											',$comment['body'],'
											<div class="breeze_options">
												<span class="time_elapsed">', $comment['time'] ,'</span>';

									// Delete comment
									if (!empty($context['permissions']['delete_comments']))
										echo '| <a href="', $scripturl , '?action=breezeajax;sa=delete;bid=', $comment['id'] ,';type=comment;profile_owner=',$context['member']['id'],'" id="', $comment['id'] ,'" class="breeze_delete_comment">', $txt['Breeze_general_delete'] ,'</a>';

									echo '
											</div>
										</div>
										<div class="clear"></div>
									</li>';
								}

							// Display the new comments
							echo '<li id="breeze_load_image_comment_', $status['id'] ,'" style="margin:auto; text-align:center;"></li>';

							echo '</ul>';

								// Post a new comment
								if (!empty($context['permissions']['post_comment']))
									echo '
								<div>
									<form action="', $scripturl , '?action=breezeajax;sa=postcomment" method="post" name="formID_', $status['id'] ,'" id="formID_', $status['id'] ,'">
										<textarea id="textboxcontent_', $status['id'] ,'" name="content" cols="40" rows="2" rel="atwhoMention"></textarea>
										<input type="hidden" value="',$status['poster_id'],'" name="status_owner_id', $status['id'] ,'" id="status_owner_id', $status['id'] ,'" />
										<input type="hidden" value="',$context['member']['id'],'" name="profile_owner_id', $status['id'] ,'" id="profile_owner_id', $status['id'] ,'" />
										<input type="hidden" value="', $status['id'] ,'" name="status_id" id="status_id" />
										<input type="hidden" value="',$user_info['id'],'" name="poster_comment_id', $status['id'] ,'" id="poster_comment_id', $status['id'] ,'" /><br />
										<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
										<input type="submit" value="', $txt['post'] ,'" class="comment_submit" id="', $status['id'] ,'" />
									</form>
								</div>';

						echo '
						</div>
						<div class="clear">xcvxcvxcvxcv</div>
					</div>
				<span class="botslice"><span></span></span>
				</li>';
		}

	// End of list
	echo '</ul>';

	// Pagination panel
	if (!empty($context['Breeze']['pagination']['panel']))
		echo '
			<div id="breeze_pagination">', $txt['pages'] ,': ', $context['Breeze']['pagination']['panel'] ,'</div>';

	// End of Wall div
	echo '
			</div>';

	// End of left side
	echo '
		</div>';

	// Right block, user's data and blocks
	echo '
	<div id="Breeze_right_block">';

	// User info, details
	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<span id="author">
				', $txt['Breeze_tabs_pinfo'], '
		</h3>
	</div>
	<div class="windowbg BreezeBlock">
		<span class="topslice">
		<span> </span>
		</span>
		<div class="content BreezeInfoBlock">';
	echo '
				<div class="username"><h4>', $context['member']['name'] ,'<br/><span class="position">', (!empty($context['member']['group']) ? $context['member']['group'] : $context['member']['post_group']), '</span></h4></div>
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

	if (!$context['user']['is_owner'] && $context['can_send_pm'])
		echo '
			<br /><a href="', $scripturl, '?action=pm;sa=send;u=', $context['id_member'], '">', $txt['profile_sendpm_short'], '</a><br />';
	echo '
			<a href="', $scripturl, '?action=profile;area=showposts;u=', $context['id_member'], '">', $txt['showPosts'], '</a><br />
			<a href="', $scripturl, '?action=profile;area=statistics;u=', $context['id_member'], '">', $txt['statPanel'], '</a>';

	echo '
			<br /></span>';

	echo'
		</div>
		<span class="botslice">
		<span> </span>
		</span>
	</div>';

	// Profile visitors
	if (!empty($context['Breeze']['views']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<span id="author">
					'. $txt['Breeze_tabs_views'] .'
			</h3>
		</div>
		<div class="windowbg2 BreezeBlock">
			<span class="topslice">
			<span> </span>
			</span>
			<div class="content BreezeList">';

		// Print a nice Ul
		echo '
				<ul class="reset">';

		// Show the profile visitors
		foreach ($context['Breeze']['views'] as $visitor)
		{

			echo '<li> ', $context['Breeze']['user_info'][$visitor['user']]['facebox'];

			// The user's name, don't forget to put a nice br to force a break line...
			echo '<br />',  $context['Breeze']['user_info'][$visitor['user']]['link'];

			// The last visit was at...?
			echo '<br />',  $context['breeze']['tools']->timeElapsed($visitor['last_view']);

			// If you're the profile owner you might want to know how many time this user has visited your profile...
			if ($context['member']['id'] == $user_info['id'])
				echo '<br />',  $txt['Breeze_user_modules_visits'] . $visitor['views'];

			// Finally, close the li
			echo '</li>';
		}

		// End the visitors list
		echo '
				</ul>';

		echo '
			</div>
			<span class="botslice">
			<span> </span>
			</span>
		</div>';
	}

	// Buddy list
	if (!empty($context['member']['options']['Breeze_enable_buddies_tab']) && !empty($context['member']['buddies']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<span id="author">
					'. $txt['Breeze_tabs_buddies'] .'
			</h3>
		</div>
		<div class="windowbg2 BreezeBlock">
			<span class="topslice">
			<span> </span>
			</span>
			<div class="content BreezeList">';

		// Print a nice Ul
		echo '
				<ul class="reset">';

		// Show the profile visitors in a big, fat echo!
		foreach ($context['member']['buddies'] as $buddy)
			echo '<li> ', $context['Breeze']['user_info'][$buddy]['facebox'] ,' <br /> ', $context['Breeze']['user_info'][$buddy]['link'] ,'</li>';

		// End the visitors list
		echo '
				</ul>';

		echo '
			</div>
			<span class="botslice">
			<span> </span>
			</span>
		</div>';
	}

	// End of right block
	echo '
		</div>';

	// End of profileview div
	echo '
	</div>';

	// Don't forget to print the users data
	if (!empty($context['Breeze']['user_info']))
		foreach ($context['Breeze']['user_info'] as $userData)
			if (!empty($userData['data']))
				echo $userData['data'];
}

function template_user_notifications()
{
	global $context, $txt, $scripturl, $user_info;

	// Get the message from the server
	template_server_response();

	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $context['page_title'] ,'</h3>
		</div>
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">';

	if (!empty($context['Breeze']['noti']))
	{
		echo '
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">', $txt['Breeze_noti_message'] ,'</th>
						<th scope="col">', $txt['Breeze_noti_markasread_title'] ,'</th>
						<th scope="col" class="last_th">', $txt['Breeze_general_delete'] ,'</th>
					</tr>
				</thead>
				<tbody>';

		foreach($context['Breeze']['noti'] as $noti)
		{
			echo '
				<tr class="windowbg" style="text-align: center">
					<td>
						', $noti['message'] ,'
					</td>
					<td>
					<a href="'. $scripturl .'?action=breezeajax;sa=notimark;content='. $noti['id'] .';user='. $user_info['id'] .'">'. (!empty($noti['viewed']) ? $txt['Breeze_noti_markasunread'] : $txt['Breeze_noti_markasread']) .'</a>
					</td>
					<td>
					<a href="'. $scripturl .'?action=breezeajax;sa=notidelete;content='. $noti['id'] .';user='. $user_info['id'] .'">', $txt['Breeze_general_delete'] ,'</a>
					</td>
				</tr>';
		}

		// Close the table
		echo '
				</tbody>
			</table><br />';
	}

	// Gotta be more social buddy...
	else
		echo $txt['Breeze_noti_none'];

		echo'
			</div>
			<span class="botslice">
				<span></span>
			</span>
		</div><br />';
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

					// Delete status
					if ($context['permissions']['delete_status'])
						echo '| <a href="', $scripturl , '?action=breezeajax;sa=delete;bid=', $context['Breeze']['single']['id'] ,';type=status;profile_owner=',$context['member']['id'],'" id="', $context['Breeze']['single']['id'] ,'" class="breeze_delete_status">', $txt['Breeze_general_delete'] ,'</a>';

					echo '
						</div>
						<hr />
						<div id="comment_flash_', $context['Breeze']['single']['id'] ,'"></div>';

					echo '
						<ul class="breeze_comments_list" id="comment_loadplace_', $context['Breeze']['single']['id'] ,'">';

					// Print out the comments
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

							// Delete comment
							if ($context['permissions']['delete_status'])
								echo '| <a href="', $scripturl , '?action=breezeajax;sa=delete;bid=', $comment['id'] ,';type=comment;profile_owner=',$context['member']['id'],'" id="', $comment['id'] ,'" class="breeze_delete_comment">', $txt['Breeze_general_delete'] ,'</a>';

							echo '
											</div>
										</div>
										<div class="clear"></div>
									</li>';
						}

						// Display the new comments
						echo '<li id="breeze_load_image_comment_', $context['Breeze']['single']['id'] ,'" style="margin:auto; text-align:center;"></li>';

						echo '</ul>';

							// Post a new comment
							if ($context['permissions']['post_comment'])
								echo '<div>
								<form action="', $scripturl , '?action=breezeajax;sa=postcomment" method="post" name="formID_', $context['Breeze']['single']['id'] ,'" id="formID_', $context['Breeze']['single']['id'] ,'">
									<textarea id="textboxcontent_', $context['Breeze']['single']['id'] ,'" cols="40" rows="2"  name="content_', $context['Breeze']['single']['id'] ,'" rel="atwhoMention"></textarea>
									<input type="hidden" value="',$context['Breeze']['single']['poster_id'],'" name="status_owner_id', $context['Breeze']['single']['id'] ,'" id="status_owner_id', $context['Breeze']['single']['id'] ,'" />
									<input type="hidden" value="', $context['Breeze']['single']['owner_id'] ,'" name="profile_owner_id', $context['Breeze']['single']['id'] ,'" id="profile_owner_id', $context['Breeze']['single']['id'] ,'" />
									<input type="hidden" value="', $context['Breeze']['single']['id'] ,'" name="status_id" id="status_id" />
									<input type="hidden" value="',$user_info['id'],'" name="poster_comment_id', $context['Breeze']['single']['id'] ,'" id="poster_comment_id', $context['Breeze']['single']['id'] ,'" /><br />
									<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
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

	// Don't forget to print the users data
	if (!empty($context['Breeze']['user_info']))
		foreach ($context['Breeze']['user_info'] as $userData)
			if (!empty($userData['data']))
				echo $userData['data'];

}

function template_member_options()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// Get the message from the server
	template_server_response();

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

		// Print the form
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

function template_server_response()
{
	global $txt;

	// Get the message from the server
	$serverResponse = Breeze::sGlobals('get');

	// Show a nice confirmation message for those without JavaScript
	if ($serverResponse->getValue('m') == true)
		echo
		'<div '. ($serverResponse->getValue('e') == true ? 'class="errorbox"' : 'id="profile_success"') ,'>
			', $txt['Breeze_'. $serverResponse->getValue('m')] ,'
		</div>';
}
