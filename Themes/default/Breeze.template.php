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
	global $txt, $context, $settings, $scripturl, $user_info;

	loadLanguage(Breeze::$name);

	// Get the message from the server
	$serverResponse = Breeze::sGlobals('get');

	// Show a nice confirmation message for those without JavaScript
	if ($serverResponse->getValue('m') == true)
		echo
	'<div '. ($serverResponse->getValue('e') == true ? 'class="errorbox"' : 'id="profile_success"') ,'>
		', $txt['Breeze_'. $serverResponse->getValue('m')] ,'
	</div>';

	// Start of profileview div
	echo '
	<div id="profileview" class="flow_auto">';

	// Left block, user's data and blocks
	echo '
		<div id="Breeze_left_block">';

	// Wall div
	echo '
			<div id="tabs_wall">
				<div class="windowbg2">
					<span class="topslice">
						<span></span>
					</span>
				<div class="content">';

			// Main content

			// This is the status box,  O RLY?
			if (!empty($context['permissions']['post_status']))
				echo '<div class="breeze_user_inner">
						<div class="breeze_user_statusbox">
							<form method="post" action="', $scripturl, '?action=breezeajax;sa=post" id="status" name="form_status" class="form_status">
								<textarea cols="40" rows="5" name="content" id="content" rel="atwhoMention"></textarea>
								<input type="hidden" value="',$context['member']['id'],'" name="owner_id" id="owner_id" />
								<input type="hidden" value="',$user_info['id'],'" name="poster_id" id="poster_id" /><br />
								<input type="submit" value="', $txt['post'] ,'" name="submit" class="status_button"/>
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
						echo '<ul class="breeze_comments_list" id="comment_loadplace_', $status['id'] ,'">';

							// Print out the comments
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
									echo '<div>
									<form action="', $scripturl , '?action=breezeajax;sa=postcomment" method="post" name="formID_', $status['id'] ,'" id="formID_', $status['id'] ,'">
										<textarea id="textboxcontent_', $status['id'] ,'" name="content" cols="40" rows="2" rel="atwhoMention"></textarea>
										<input type="hidden" value="',$status['poster_id'],'" name="status_owner_id', $status['id'] ,'" id="status_owner_id', $status['id'] ,'" />
										<input type="hidden" value="',$context['member']['id'],'" name="profile_owner_id', $status['id'] ,'" id="profile_owner_id', $status['id'] ,'" />
										<input type="hidden" value="', $status['id'] ,'" name="status_id" id="status_id" />
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

	// Right block, user's status and comments
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
	<div class="windowbg">
		<span class="topslice">
		<span> </span>
		</span>
		<div class="content">';
	echo '
			<div style="float:auto;margin:auto;text-align:center;padding-bottom:5px;">
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

	echo '
		</div>';

	// User basic info
	echo '
		<div style="float:auto;">
			<dl>';

	if ($context['user']['is_owner'] || $context['user']['is_admin'])
		echo '
				<dt>', $txt['username'], ': </dt>
				<dd>', $context['member']['username'], '</dd>';

	if (!isset($context['disabled_fields']['posts']))
		echo '
				<dt>', $txt['profile_posts'], ': </dt>
				<dd>', $context['member']['posts'], ' (', $context['member']['posts_per_day'], ' ', $txt['posts_per_day'], ')</dd>';

	// Only show the email address fully if it's not hidden - and we reveal the email.
	if ($context['member']['show_email'] == 'yes')
		echo '
				<dt>', $txt['email'], ': </dt>
				<dd><a href="', $scripturl, '?action=emailuser;sa=email;uid=', $context['member']['id'], '">', $context['member']['email'], '</a></dd>';

	// ... Or if the one looking at the profile is an admin they can see it anyway.
	elseif ($context['member']['show_email'] == 'yes_permission_override')
		echo '
				<dt>', $txt['email'], ': </dt>
				<dd><em><a href="', $scripturl, '?action=emailuser;sa=email;uid=', $context['member']['id'], '">', $context['member']['email'], '</a></em></dd>';

	if (!empty($modSettings['titlesEnable']) && !empty($context['member']['title']))
		echo '
				<dt>', $txt['custom_title'], ': </dt>
				<dd>', $context['member']['title'], '</dd>';

	if (!empty($context['member']['blurb']))
		echo '
				<dt>', $txt['personal_text'], ': </dt>
				<dd>', $context['member']['blurb'], '</dd>';

	// If karma enabled show the members karma.
	if ($modSettings['karmaMode'] == '1')
		echo '
				<dt>', $modSettings['karmaLabel'], ' </dt>
				<dd>', ($context['member']['karma']['good'] - $context['member']['karma']['bad']), '</dd>';

	elseif ($modSettings['karmaMode'] == '2')
		echo '
				<dt>', $modSettings['karmaLabel'], ' </dt>
				<dd>+', $context['member']['karma']['good'], '/-', $context['member']['karma']['bad'], '</dd>';

	if (!isset($context['disabled_fields']['gender']) && !empty($context['member']['gender']['name']))
		echo '
				<dt>', $txt['gender'], ': </dt>
				<dd>', $context['member']['gender']['name'], '</dd>';

	echo '
				<dt>', $txt['age'], ':</dt>
				<dd>', $context['member']['age'] . ($context['member']['today_is_birthday'] ? ' &nbsp; <img src="' . $settings['images_url'] . '/cake.png" alt="" />' : ''), '</dd>';

	if (!isset($context['disabled_fields']['location']) && !empty($context['member']['location']))
		echo '
				<dt>', $txt['location'], ':</dt>
				<dd>', $context['member']['location'], '</dd>';

	echo '
			</dl>';

	// Any custom fields for standard placement?
	if (!empty($context['custom_fields']))
	{
		$shown = false;
		foreach ($context['custom_fields'] as $field)
		{
			if ($field['placement'] != 0 || empty($field['output_html']))
				continue;

			if (empty($shown))
			{
			echo '
			<dl>';
				$shown = true;
			}

			echo '
				<dt>', $field['name'], ':</dt>
				<dd>', $field['output_html'], '</dd>';
		}

		if (!empty($shown))
			echo '
			</dl>';
	}

	echo '
			<dl class="noborder">';

	// Can they view/issue a warning?
	if ($context['can_view_warning'] && $context['member']['warning'])
	{
		echo '
				<dt>', $txt['profile_warning_level'], ': </dt>
				<dd>
					<a href="', $scripturl, '?action=profile;u=', $context['id_member'], ';area=', $context['can_issue_warning'] ? 'issuewarning' : 'viewwarning', '">', $context['member']['warning'], '%</a>';

		// Can we provide information on what this means?
		if (!empty($context['warning_status']))
			echo '
					<span class="smalltext">(', $context['warning_status'], ')</span>';

		echo '
				</dd>';
	}

	// Is this member requiring activation and/or banned?
	if (!empty($context['activate_message']) || !empty($context['member']['bans']))
	{

		// If the person looking at the summary has permission, and the account isn't activated, give the viewer the ability to do it themselves.
		if (!empty($context['activate_message']))
			echo '
				<dt class="clear"><span class="alert">', $context['activate_message'], '</span>&nbsp;(<a href="' . $scripturl . '?action=profile;save;area=activateaccount;u=' . $context['id_member'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '"', ($context['activate_type'] == 4 ? ' onclick="return confirm(\'' . $txt['profileConfirm'] . '\');"' : ''), '>', $context['activate_link_text'], '</a>)</dt>';

		// If the current member is banned, show a message and possibly a link to the ban.
		if (!empty($context['member']['bans']))
		{
			echo '
				<dt class="clear"><span class="alert">', $txt['user_is_banned'], '</span>&nbsp;[<a href="#" onclick="document.getElementById(\'ban_info\').style.display = document.getElementById(\'ban_info\').style.display == \'none\' ? \'\' : \'none\';return false;">' . $txt['view_ban'] . '</a>]</dt>
				<dt class="clear" id="ban_info" style="display: none;">
					<strong>', $txt['user_banned_by_following'], ':</strong>';

			foreach ($context['member']['bans'] as $ban)
				echo '
					<br /><span class="smalltext">', $ban['explanation'], '</span>';

			echo '
				</dt>';
		}
	}

	echo '
				<dt>', $txt['date_registered'], ': </dt>
				<dd>', $context['member']['registered'], '</dd>';

	// If the person looking is allowed, they can check the members IP address and hostname.
	if ($context['can_see_ip'])
	{
		if (!empty($context['member']['ip']))
		echo '
				<dt>', $txt['ip'], ': </dt>
				<dd><a href="', $scripturl, '?action=profile;area=tracking;sa=ip;searchip=', $context['member']['ip'], ';u=', $context['member']['id'], '">', $context['member']['ip'], '</a></dd>';

		if (empty($modSettings['disableHostnameLookup']) && !empty($context['member']['ip']))
			echo '
				<dt>', $txt['hostname'], ': </dt>
				<dd>', $context['member']['hostname'], '</dd>';
	}

	echo '
				<dt>', $txt['local_time'], ':</dt>
				<dd>', $context['member']['local_time'], '</dd>';

	if (!empty($modSettings['userLanguage']) && !empty($context['member']['language']))
		echo '
				<dt>', $txt['language'], ':</dt>
				<dd>', $context['member']['language'], '</dd>';

	echo '
				<dt>', $txt['lastLoggedIn'], ': </dt>
				<dd>', $context['member']['last_login'], '</dd>
			</dl>';

	// Are there any custom profile fields for the summary?
	if (!empty($context['custom_fields']))
	{
		$shown = false;
		foreach ($context['custom_fields'] as $field)
		{
			if ($field['placement'] != 2 || empty($field['output_html']))
				continue;
			if (empty($shown))
			{
				$shown = true;
				echo '
			<div class="custom_fields_above_signature">
				<ul class="reset nolist">';
			}
			echo '
					<li>', $field['output_html'], '</li>';
		}
		if ($shown)
				echo '
				</ul>
			</div>';
	}

	// End of user basic info dic
	echo '
		</div>';

	// Clear both
	echo '
		<div class="clear"></div>';

	echo'
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
		<div class="windowbg2">
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

			echo '<li> ', $context['Breeze']['user_info'][$visitor['user']]['facebox'] ,' </li>';
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
	$serverResponse = Breeze::sGlobals('get');

	// Show a nice confirmation message for those without JavaScript
	if ($serverResponse->getValue('m') == true)
		echo
		'<div class="', $serverResponse->getValue('e') == true ? 'errorbox' : 'windowbg' ,'" id="profile_success">
			', $txt['Breeze_'. $serverResponse->getValue('m') .'_after'] ,'
		</div>';

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

	// Get the message from the server
	$serverResponse = Breeze::sGlobals('get');

	// Show a nice confirmation message for those without JavaScript
	if ($serverResponse->getValue('m') == true)
		echo
		'<div '. ($serverResponse->getValue('e') == true ? 'class="errorbox"' : 'id="profile_success"') ,'>
			', $txt['Breeze_'. $serverResponse->getValue('m')] ,'
		</div>';

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
	$serverResponse = Breeze::sGlobals('get');

	// Show a nice confirmation message for those without JavaScript
	if ($serverResponse->getValue('m') == true)
		echo
		'<div '. ($serverResponse->getValue('e') == true ? 'class="errorbox"' : 'id="profile_success"') ,'>
			', $txt['Breeze_'. $serverResponse->getValue('m')] ,'
		</div>';

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
