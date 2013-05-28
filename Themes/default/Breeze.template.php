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

// General wall...
	// This will be moved to its own template... eventually
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

	echo '
		<div id="profileview" class="flow_auto">
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

		echo '
			</div>';

	// End of right side

	// Left side
	echo '
	<div class="tabContainer" id="detailedinfo">
	<div id="tab-container" class="tab-container">';

	// Scroll to top
	echo '
			<p id="breezeTop">
				<a href="#wrapper"><span></span>', $txt['Breeze_goTop'] ,'</a>
			</p>';

	// Links for tabs show it if there is at least 1 tab
	if (!empty($context['member']['options']['Breeze_enable_visits_tab']) || !empty($context['member']['options']['Breeze_enable_buddies_tab']))
		echo '
		<ul class="etabs dropmenu">
			<li><a href="#tabs_wall" class="active firstlevel"><span class="firstlevel">', $txt['Breeze_tabs_wall'] ,'</span></a></li>
			', (!empty($context['member']['options']['Breeze_enable_visits_tab']) ? '
			<li>
				<a href="#tabs_views" class="firstlevel"><span class="firstlevel">'. $txt['Breeze_tabs_views'] .'</span></a>
			</li>' : '') ,'
			', (!empty($context['member']['options']['Breeze_enable_buddies_tab']) ? '
			<li>
				<a href="#tabs_buddies" class="firstlevel"><span class="firstlevel">'. $txt['Breeze_tabs_buddies'] .'</span></a>
			</li>' : '') ,'
		</ul>';


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
	echo '<span id="breeze_load_image"></span>
	<ul class="breeze_status" id="breeze_display_status">';

	// Status and comments
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
		echo '<div id="breeze_pagination">', $txt['pages'] ,': ', $context['Breeze']['pagination']['panel'] ,'</div>';

	// End of Wall div
	echo '
			</div>';

	// Profile visitors
	if (!empty($context['member']['options']['Breeze_enable_visits_tab']) && !empty($context['Breeze']['views']))
	{
		echo '
				<div id="tabs_views" class="hide">
					<ul class="reset breeze_top_profile_views">';

		foreach ($context['Breeze']['views'] as $profile_views)
			echo '
						<li class="windowbg2 breeze_profile_views_block">
							<div class="cat_bar">
								<h3 class="catbg">', $context['Breeze']['user_info'][$profile_views['user']]['link'] ,'</h3>
							</div>
							<span class="upperframe">
								<span></span>
							</span>
							<div class="roundframe">
								<p class="breeze_profile_views_avatar">', $context['Breeze']['user_info'][$profile_views['user']]['facebox'] ,'</p>
								<p>', $txt['Breeze_general_last_view'] ,': ', $context['breeze']['tools']->timeElapsed($profile_views['last_view']) ,'</p>
							</div>
							<span class="lowerframe">
								<span></span>
							</span>
						</li>';

		echo '
					</ul>
				</div>';
	}

	// User doesn't have any visitors
	else
		echo '<p class="hide windowbg description" id="tabs_views">', $txt['Breeze_user_modules_visits_none'] ,'</p>';

	// End of profile visitors

	// Buddies tab
	if (!empty($context['member']['options']['Breeze_enable_buddies_tab']) && !empty($context['member']['buddies']))
	{
		echo '
				<div id="tabs_buddies" class="hide">
					<ul class="reset breeze_top_profile_views">';

		foreach ($context['member']['buddies'] as $buddies)
			echo '
						<li class="windowbg2 breeze_profile_views_block">
							<div class="cat_bar">
								<h3 class="catbg">', $context['Breeze']['user_info'][$buddies]['link'] ,'</h3>
							</div>
							<span class="upperframe">
								<span></span>
							</span>
							<div class="roundframe">
								<p class="breeze_profile_views_avatar">', $context['Breeze']['user_info'][$buddies]['facebox'] ,'</p>
							</div>
							<span class="lowerframe">
								<span></span>
							</span>
						</li>';

		echo '
					</ul>
				</div>';
	}

	// User doesn't have any visits
	else
		echo '<p class="hide windowbg description" id="tabs_views">', $txt['Breeze_user_modules_buddies_none']  ,'</p>';
	// End of buddies tab

	// End of left side
	echo '
	</div>
	</div>
		<div class="clear"></div>
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
					<a href="'. $scripturl .'?action=breezeajax;sa=notimark;content='. $noti['id'] .';user='. $user_info['id'] .'" id="markread_'. $noti['id'] .'" class="Breeze_markRead">'. (!empty($noti['viewed']) ? $txt['Breeze_noti_markasunread'] : $txt['Breeze_noti_markasread']) .'</a>
					</td>
					<td>
					<a href="javascript:void(0)" id="delete_'. $noti['id'] .'" class="Breeze_delete">', $txt['Breeze_general_delete'] ,'</a>
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
