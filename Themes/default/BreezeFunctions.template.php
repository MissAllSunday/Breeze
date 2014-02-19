<?php

/**
 * BreezeFunctions.template
 *
 * The purpose of this file is to modularize some of the most used blocks of code, the point is to reduce view code and maximize and re-use as much code as possible
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
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

function breeze_status($data, $returnVar = false)
{
	global $context, $txt, $user_info, $scripturl;

	$echo = '';

	// New ajax status here DO NOT MODIFY THIS UNLESS YOU KNOW WHAT YOU'RE DOING and even if you do, DON'T MODIFY THIS
	$echo .= '
		<div id="breeze_load_image"></div>
		<ul class="breeze_status" id="breeze_display_status">';

	// Status and comments
	foreach ($data as $status)
	{
		// Yep, this is a perfect place to get permissions...
		$canHas = $context['Breeze']['tools']->permissions('Status', $status['owner_id'], $status['poster_id']);

		$echo .= '
			<li class="windowbg status_breeze" id ="status_id_'. $status['id'] .'">';

		// If we're on the general wall, show a nice bar indicating where this status come from...
		if (!empty($context['Breeze']['comingFrom']) && $context['Breeze']['comingFrom'] == 'wall')
			$echo .= '
				<div class="cat_bar">
					<h3 class="catbg">
						<span id="author">
							'. sprintf($txt['Breeze_general_posted_on'], $context['Breeze']['user_info'][$status['owner_id']]['link']) .'
					</h3>
				</div>';

		$echo .= '
				<span class="topslice"><span></span></span>
					<div class="breeze_user_inner">
						<div class="breeze_user_status_avatar">
							'. $context['Breeze']['user_info'][$status['poster_id']]['facebox'] .'<br />
							'. $context['Breeze']['user_info'][$status['poster_id']]['link'] .'
						</div>
						<div class="breeze_user_status_comment">
							'. $status['body'] .'
							<div class="breeze_options">
								<span class="time_elapsed" title="'. timeformat($status['time_raw'], false) .'">'. $status['time'] .' </span>';

		// Delete status
		if ($canHas['delete'])
			$echo .=
								'| <a href="'. $scripturl .'?action=breezeajax;sa=delete;bid='. $status['id'] .';type=status;profileOwner='. $status['owner_id'] .';poster='. $status['poster_id'] .''. (!empty($context['Breeze']['comingFrom']) ? ';rf='. $context['Breeze']['comingFrom'] : '') .'" id="deleteStatus_'. $status['id'] .'" class="breeze_delete_status">'. $txt['Breeze_general_delete'] .'</a>';

		// Modify? maybe someday...

		$echo .= '
							</div>
							<hr />
							<div id="comment_flash_'. $status['id'] .'"></div>';

		$echo .= '
								<ul class="breeze_comments_list" id="comment_loadplace_'. $status['id'] .'">';

		// Print out the comments
		if (!empty($status['comments']))
				$echo .= breeze_comment($status['comments'], true);

		$echo .= '
								</ul>';

		$echo .= '
								<div id="breeze_load_image_comment_'. $status['id'] .'" style="margin:auto; text-align:center;"></div>';

		// Post a new comment
		if ($canHas['post'])
		{
			$echo .= '
								<div class="post_comment">';

			// Show a nice avatar next to the post form
			if (!empty($context['Breeze']['user_info'][$user_info['id']]['facebox']))
				$echo .=  $context['Breeze']['user_info'][$user_info['id']]['facebox'];

			// The actual post form
				$echo .= '
									<form action="'. $scripturl .'?action=breezeajax;sa=postcomment'. (!empty($context['Breeze']['comingFrom']) ? ';rf='. $context['Breeze']['comingFrom'] : '') .'" method="post" name="form_comment_'. $status['id'] .'" id="form_comment_'. $status['id'] .'" class="form_comment">
										<textarea name="commentContent" id="commentContent_'. $status['id'] .'" cols="40" rows="2" rel="atwhoMention"></textarea>
										<input type="hidden" value="'. $status['poster_id'] .'" name="commentStatusPoster" id="commentStatusPoster_'. $status['id'] .'" />
										<input type="hidden" value="'. $user_info['id'] .'" name="commentPoster" id="commentPoster_'. $status['id'] .'" />
										<input type="hidden" value="'. $status['id'] .'" name="commentStatus" id="commentStatus_'. $status['id'] .'" />
										<input type="hidden" value="'. $status['poster_id'] .'" name="commentOwner" id="commentOwner_'. $status['id'] .'" /><br />
										<input type="hidden" id="'. $context['session_var'] .'" name="'. $context['session_var'] .'" value="'. $context['session_id'] .'" />
										<input type="submit" value="'. $txt['post'] .'" class="comment_submit" name="commentSubmit" id="commentSubmit_'. $status['id'] .'" />
									</form>';

			// End of div post_comment
				$echo .= '
								</div>';
		}

			$echo .= '
						</div>
						<div class="clear"></div>
					</div>
				<span class="botslice"><span></span></span>
			</li>';
	}

	// End of list
	$echo .= '
		</ul>';

	// What are we gonna do?
	if ($returnVar)
		return $echo;

	else
		echo $echo;
}

function breeze_comment($comments, $returnVar = false)
{
	global $context, $txt, $scripturl, $user_info;

	$echo = '';

	foreach ($comments as $comment)
	{
		// Yup, I didn't think about a better place for some logic than a template file... am I awesome or what!
		$canHas = $context['Breeze']['tools']->permissions('Comments', $comment['profile_id'], $comment['poster_id']);

		$echo .= '
		<li class="windowbg2" id ="comment_id_'. $comment['id'] .'">
			<div class="breeze_user_comment_avatar">
					'. $context['Breeze']['user_info'][$comment['poster_id']]['facebox'] .'<br />
					'. $context['Breeze']['user_info'][$comment['poster_id']]['link'] .'
			</div>
			<div class="breeze_user_comment_comment">
				'. $comment['body'] .'
				<div class="breeze_options">
					<span class="time_elapsed" title="'. timeformat($comment['time_raw'], false) .'">'. $comment['time'] .'</span>';

		// Delete comment
		if ($canHas['delete'])
			$echo .= '| <a href="'. $scripturl .'?action=breezeajax;sa=delete;bid='. $comment['id'] .';type=comments;poster='. $comment['poster_id'] .';profileOwner='. $comment['profile_id'] .''. (!empty($context['Breeze']['comingFrom']) ? ';rf='. $context['Breeze']['comingFrom'] : '') .'" id="deleteComment_'. $comment['id'] .'" class="breeze_delete_comment">'. $txt['Breeze_general_delete'] .'</a>';

		$echo .= '
				</div>
			</div>
			<div class="clear"></div>
		</li>';
	}

	// What are we going to do?
	if ($returnVar)
		return $echo;

	else
		echo $echo;
}

function breeze_profile_owner()
{
	global $context, $txt, $settings, $scripturl;

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
	if (!empty($context['can_have_buddy']) && !$context['member']['is_owner'])
		echo '
			<br /><a href="', $scripturl, '?action=buddy;u=', $context['id_member'], ';', $context['session_var'], '=', $context['session_id'], '">[', $txt['buddy_' . ($context['member']['is_buddy'] ? 'remove' : 'add')], ']</a>';

	if (!$context['member']['is_owner'] && $context['can_send_pm'])
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
}

function breeze_activity($data)
{
	global $context, $txt;

	if (empty($data))
		return false;

	$counter = 1;

	echo '
		<div class="content">
			<ul class="reset">';

	foreach ($data as $act)
	{
		echo '
				<li class="activityLI">
						<div class="activityContent windowbg', ($counter = !$counter ? '2' : '') ,'">
							<span class="topslice">
								<span> </span>
							</span>
							<div class="activityInner">
								<span class="activityIcon ', $act['type'] ,'"></span>
								<span class="time_elapsed" title="'. timeformat($act['time_raw'], false) .'">'. $act['time'] .' </span><br />
								', $act['content']['message'] ,'<br />
								', $act['content']['link'] ,'
							</div>
							<span class="botslice">
								<span></span>
							</span>
						</div>
				</li>';
	}

	// Close the ul
	echo '
			</ul>
		</div>';
}

function breeze_user_info()
{
	global $context;

	if (!empty($context['Breeze']['user_info']))
		foreach ($context['Breeze']['user_info'] as $userData)
			if (!empty($userData['data']))
				echo $userData['data'];
}

function breeze_user_list($list, $type = 'buddy')
{
	global $context, $user_info, $txt;

	// You have too many buddies/visitors pal!
	if ($context['Breeze']['compact'][$type])
	{

		echo '<ol>';

		foreach ($list as $u)
		{
			// Trickery...
			$user = $type == 'visitors' ? $u['user'] : $u;

			echo '<li>', $context['Breeze']['user_info'][$user]['link'] ,'</li>';
		}

		echo '</ul>';
	}

	// Print a nice Ul
	else
	{
		echo '
			<ul class="reset">';

		// Show the profile visitors in a big, fat echo!
		foreach ($list as $u)
		{
			// Trickery...
			$user = $type == 'visitors' ? $u['user'] : $u;

			echo '
				<li> ', $context['Breeze']['user_info'][$user]['facebox'] ,' <br /> ', $context['Breeze']['user_info'][$user]['link'];

			// Are we showing the visitors? if so, show some more info!
			if ($type == 'visitors')
			{
				echo '
						<br />',  $context['Breeze']['tools']->timeElapsed($u['last_view']);

				// If you're the profile owner you might want to know how many time this user has visited your profile...
				if ($context['member']['id'] == $user_info['id'])
					echo '
						<br />',  $txt['Breeze_user_modules_visitors'] . $u['views'];
			}

			// close the li
			echo '</li>';
		}

		// End the buddies list
		echo '
			</ul>';
	}
}

function breeze_server_response()
{
	global $txt;

	// Just to be sure...
	loadLanguage(Breeze::$name);

	// Get the message from the server
	$serverResponse = Breeze::data('get');

	$type = $serverResponse->get('mstype');
	$message = $serverResponse->get('msmessage');

	// Show a nice confirmation message for those without JavaScript
	if (!empty($type) && !empty($message))
		echo
		'<div '. ($type == 'error' ? 'class="errorbox"' : 'id="profile_success"') ,'>
			', $txt['Breeze_'. $type .'_'. $message] ,'
		</div>';
}
