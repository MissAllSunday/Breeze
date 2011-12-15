<?php

/**
 * Breeze_
 * 
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica González
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
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

	/* The purpose of this file is to centralize the user info,
	helps to avoid having to write code over and over again */
class Breeze_UserInfo
{
	public static function Profile($user, $id = false)
	{
		global $txt, $context, $settings, $scripturl, $user_info;

		/* Can't do much if the user id is empty... */
		if (empty($user))
			return;

		/* We load the data only if the user hasn't been loaded yet and we got and ID rather than an array
			if we got an array, the user was already loaded*/
		if ($id == true && isset($memberContext[$user]) == false)
		{
			global $memberContext, $context;

			loadMemberData($user, false, 'profile');
			loadMemberContext($user);
			$user = $memberContext[$user];
		}

		$return = '';
		$user['is_owner'] = $user['id'] == $user_info['id'];
		
		/* Sometimes we just want the link */
		$context['Breeze']['user_info']['link'][$user['id']] = $user['link'];
		
		/* ...or the name */
		$context['Breeze']['user_info']['name'][$user['id']] = $user['name'];

		/* It all starts with the user's avatar or username... */
		$return .= '<a href="#facebox_'.$user['id'].'" rel="facebox">'.(!empty($user['avatar']['href']) ? '<img src="'.$user['avatar']['href'].'" width="50px" />' : $user['link']) .'</a>
		<div id="facebox_'.$user['id'].'" style="display:none;">
			<div class="windowbg">
				<span class="topslice"><span></span></span>
				<div style="margin:3px;padding-right:15px;padding-left:5px;float:left;min-height:100px;">
					'.$user['avatar']['image'].'<br />'.$user['link'].'';

						// Can they add this member as a buddy?
	if (!empty($context['can_have_buddy']) && !$user['is_owner'])
		$return .= '
				<br /><a href="'. $scripturl. '?action=buddy;u='.$user['id']. ';'.$context['session_var']. '='.$context['session_id'].'">['.$txt['buddy_' . ($context['member']['is_buddy'] ? 'remove' : 'add')].']</a>';

				$return .= '</div>
				<div>
					<ul class="breeze_user_left_info">';

		// Show the member's primary group (like 'Administrator') if they have one.
		if (!empty($user['group']))
			$return .= '<li class="membergroup"><span style="color:'.$user['group_color'].';">'. $user['group']. '</span></li>';

		// Show how many posts they have made.
		if (!isset($context['disabled_fields']['posts']))
			$return .= '<li class="postcount">'. $txt['member_postcount']. ': '. $user['posts']. '</li>';

		// Don't show these things for guests.
		if (!$user['is_guest'])
		{

			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $user['group'] == '') && $user['post_group'] != '')
				$return .= '<li class="postgroup">'. $user['post_group']. '</li>';

			$return .= '<li class="stars">'. $user['group_stars']. '</li>';

			// Show the member's gender icon?
			if (!empty($settings['show_gender']) && $user['gender']['image'] != '' && !isset($context['disabled_fields']['gender']))
				$return .= '<li class="gender">'. $txt['gender']. ': '. $user['gender']['image']. '</li>';

			// Show their personal text?
			if (!empty($settings['show_blurb']) && $user['blurb'] != '')
				$return .= '<li class="blurb">'. $user['blurb']. '</li>';

			// Any custom fields to show as icons?
			if (!empty($user['custom_fields']))
			{
				$shown = false;
				foreach ($user['custom_fields'] as $custom)
				{
					if ($custom['placement'] != 1 || empty($custom['value']))
						continue;
					if (empty($shown))
					{
						$shown = true;
						$return .= '<li class="im_icons">
									<ul>';
					}
					$return .= '<li>'. $custom['value']. '</li>';
				}
				if ($shown)
					$return .= '</ul>
								</li>';
			}

			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{
				$return .= '<li class="profile">
									<ul>';

				// Don't show an icon if they haven't specified a website.
				if ($user['website']['url'] != '' && !isset($context['disabled_fields']['website']))
					$return .= '<li><a href="'. $user['website']['url']. '" title="' . $user['website']['title'] . '" target="_blank" class="new_win">'. ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $user['website']['title'] . '" />' : $txt['www']). '</a></li>';

				// Don't show the email address if they want it hidden.
				if (in_array($user['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
					$return .= '<li><a href="'. $scripturl . '?action=emailuser;sa=email;msg='. $user['id']. '" rel="nofollow">'. ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']). '</a></li>';

				$return .= '</ul>
				</li>';
			}

			// Any custom fields for standard placement?
			if (!empty($user['custom_fields']))
			{
				foreach ($user['custom_fields'] as $custom)
					if (empty($custom['placement']) || empty($custom['value']))
						$return .= '
								<li class="custom">'. $custom['title'] . ': '. $custom['value'] . '</li>';
			}
		}

		// Otherwise, show the guest's email.
		elseif (!empty($user['email']) && in_array($user['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
			$return .= '<li class="email"><a href="'. $scripturl . '?action=emailuser;sa=email;msg='. $context['id'] . '" rel="nofollow">'. ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']). '</a></li>';



		/* Info list end */
		$return .= '</ul>
				</div>
				<div class="clear"></div>
			<span class="botslice"><span></span></span>
			</div>
		</div>';

		return $return;
	}
}