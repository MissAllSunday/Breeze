<?php

/**
 * BreezeDisplay.template
 *
 * The purpose of this file is to show the admin section for the mod's settings
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <missallsunday@simplemachines.org>
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

function template_main()
{
	global $scripturl, $user_info, $context;

	switch ($context['Breeze']['type'])
	{
		case 'status':
			$return = '
	<li class="windowbg" id ="status_id_'. $context['Breeze']['params']['id'] .'">
		<span class="topslice">
			<span></span>
		</span>
		<div class="breeze_user_inner">
			<div class="breeze_user_status_avatar">
				'. $context['Breeze']['user_info'][$context['Breeze']['params']['poster_id']]['facebox'] .'
			</div>
			<div class="breeze_user_status_comment">
				'. $context['Breeze']['params']['body'] .'
				<div class="breeze_options"><span class="time_elapsed">'. $context['Breeze']['params']['time'] .' </span>';

				// Delete link
				if ($context['Breeze']['permissions']['deleteStatus'])
					$return .= '| <a href="javascript:void(0)" id="'. $context['Breeze']['params']['id'] .'" class="breeze_delete_status">'. $context['Breeze']['text']->getText('general_delete') .'</a>';

				$return .= '</div>
				<hr />
				<div id="comment_flash_'. $context['Breeze']['params']['id'] .'"></div>';

				$return .= '<ul class="breeze_comments_list" id="comment_loadplace_'. $context['Breeze']['params']['id'] .'">';

					// New status don't have comments...

					// display the new comments ^o^
					$return .= '
					<li id="breeze_load_image_comment_'. $context['Breeze']['params']['id'] .'" style="margin:auto; text-align:center;"></li>';

					// Close the list
					$return .= '</ul>';

					// display the form for new comments
					if ($context['Breeze']['permissions']['postcomments'])
						$return .= '
						<span><form action="'. $scripturl. '?action=breezeajax;sa=postcomment" method="post" name="formID_'. $context['Breeze']['params']['id'] .'" id="formID_'. $context['Breeze']['params']['id'] .'">
							<textarea id="textboxcontent_'. $context['Breeze']['params']['id'] .'" cols="40" rows="2"></textarea>
							<input type="hidden" value="'. $context['Breeze']['params']['poster_id'] .'" name="status_owner_id'. $context['Breeze']['params']['id'] .'" id="status_owner_id'. $context['Breeze']['params']['id'] .'" />
							<input type="hidden" value="'. $context['Breeze']['params']['owner_id'] .'" name="profile_owner_id'. $context['Breeze']['params']['id'] .'" id="profile_owner_id'. $context['Breeze']['params']['id'] .'" />
							<input type="hidden" value="'. $context['Breeze']['params']['id'] .'" name="status_id'. $context['Breeze']['params']['id'] .'" id="status_id'. $context['Breeze']['params']['id'] .'" />
							<input type="hidden" value="'. $user_info['id'] .'" name="poster_comment_id'. $context['Breeze']['params']['id'] .'" id="poster_comment_id'. $context['Breeze']['params']['id'] .'" /><br />
							<input type="submit" value="Comment" class="comment_submit" id="'. $context['Breeze']['params']['id'] .'" />
						</form></span>';


				// Close the div
				$return .= '</div>
				<div class="clear"></div>
			</div>
		<span class="botslice">
			<span></span>
		</span>
		</li>';
			break;
		case 'comment':
			$return = '
				<li class="windowbg2" id ="comment_id_'. $context['Breeze']['params']['id'] .'">
					<div class="breeze_user_comment_avatar">
						'. $context['Breeze']['user_info'][$context['Breeze']['params']['poster_id']]['facebox'] .'<br />
					</div>
					<div class="breeze_user_comment_comment">
						'. $context['Breeze']['params']['body'] .'
						<div class="breeze_options">
							<span class="time_elapsed">'. $context['Breeze']['params']['time'] .'</span> | <a href="javascript:void(0)" id="'. $context['Breeze']['params']['id'] .'" class="breeze_delete_comment">Delete</a>
						</div>
					</div>
					<div class="clear"></div>
				</li>';
			break;
	}

	return $return;
}

function template_userInfo()
{
	global $txt, $context, $settings, $scripturl, $user_info, $context;

	// Sometimes we just want the link
	if (!empty($context['user']['info']['link']))
		$context['Breeze']['user_info'][$context['user']['info']['id']]['link'] = $context['user']['info']['link'];

	// ...or the name
	if (!empty($context['user']['info']['name']))
		$context['Breeze']['user_info'][$context['user']['info']['id']]['name'] = $context['user']['info']['name'];

	// It all starts with the user's avatar or username...
	$context['Breeze']['user_info'][$context['user']['info']['id']]['facebox'] .= (!empty($context['user']['info']['avatar']['href']) ? '<a href="#facebox_'. $context['user']['info']['id'] .'" rel="facebox"><img src="'.$context['user']['info']['avatar']['href'].'" width="50px" /></a>' : $context['user']['info']['link']);

	// Set the data
	$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] = '
	<div id="facebox_'. $context['user']['info']['id'] .'" style="display:none;">
		<div class="windowbg">
			<span class="topslice">
				<span></span>
			</span>
			<div style="margin:3px;padding-right:15px;padding-left:5px;float:left;min-height:100px;">
				'.($context['user']['info']['avatar']['image'] ? $context['user']['info']['avatar']['image'] : '').'<br />'. $context['user']['info']['link'];

	$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '</div>
			<div>
				<ul class="breeze_user_left_info">';

	// Show the member's primary group (like 'Administrator') if they have one.
	if (!empty($context['user']['info']['group']))
		$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '<li class="membergroup"><span style="color:'.$context['user']['info']['group_color'].';">'. $context['user']['info']['group']. '</span></li>';

	// Show how many posts they have made.
	if (!isset($context['disabled_fields']['posts']))
		$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '<li class="postcount">'. $txt['member_postcount']. ': '. $context['user']['info']['posts']. '</li>';

	// Don't show these things for guests.
	if (!$context['user']['info']['is_guest'])
	{

		// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
		if ((empty($settings['hide_post_group']) || $context['user']['info']['group'] == '') && $context['user']['info']['post_group'] != '')
			$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '<li class="postgroup">'. $context['user']['info']['post_group']. '</li>';

		$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '<li class="stars">'. $context['user']['info']['group_stars']. '</li>';

		// Show the member's gender icon?
		if (!empty($settings['show_gender']) && $context['user']['info']['gender']['image'] != '' && !isset($context['disabled_fields']['gender']))
			$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '<li class="gender">'. $txt['gender']. ': '. $context['user']['info']['gender']['image']. '</li>';

		// Show their personal text?
		if (!empty($settings['show_blurb']) && $context['user']['info']['blurb'] != '')
			$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '<li class="blurb">'. $context['user']['info']['blurb']. '</li>';

		// Any custom fields to show as icons?
		if (!empty($context['user']['info']['custom_fields']))
		{
			$shown = false;
			foreach ($context['user']['info']['custom_fields'] as $custom)
			{
				if ($custom['placement'] != 1 || empty($custom['value']))
					continue;
				if (empty($shown))
				{
					$shown = true;
					$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '<li class="im_icons">
								<ul>';
				}
				$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '<li>'. $custom['value']. '</li>';
			}
			if ($shown)
				$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '</ul>
							</li>';
		}

		// Show the profile, website, email address, and personal message buttons.
		if ($settings['show_profile_buttons'])
		{
			$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '<li class="profile">
								<ul>';

			// Don't show an icon if they haven't specified a website.
			if ($context['user']['info']['website']['url'] != '' && !isset($context['disabled_fields']['website']))
				$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '<li><a href="'. $context['user']['info']['website']['url']. '" title="' . $context['user']['info']['website']['title'] . '" target="_blank" class="new_win">'. ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $context['user']['info']['website']['title'] . '" />' : $txt['www']). '</a></li>';

			// Don't show the email address if they want it hidden.
			if (in_array($context['user']['info']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
				$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '<li><a href="'. $scripturl . '?action=emailuser;sa=email;msg='. $context['user']['info']['id']. '" rel="nofollow">'. ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']). '</a></li>';

			$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '</ul>
			</li>';
		}

		// Any custom fields for standard placement?
		if (!empty($context['user']['info']['custom_fields']))
		{
			foreach ($context['user']['info']['custom_fields'] as $custom)
				if (empty($custom['placement']) || empty($custom['value']))
					$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '
							<li class="custom">'. $custom['title'] . ': '. $custom['value'] . '</li>';
		}
	}

	// Otherwise, show the guest's email.
	elseif (!empty($context['user']['info']['email']) && in_array($context['user']['info']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
		$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '<li class="email"><a href="'. $scripturl . '?action=emailuser;sa=email;msg='. $context['id'] . '" rel="nofollow">'. ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']). '</a></li>';



	// Info list end
	$context['Breeze']['user_info'][$context['user']['info']['id']]['data'] .= '</ul>
			</div>
			<div class="clear"></div>
		<span class="botslice"><span></span></span>
		</div>
	</div>';
}
