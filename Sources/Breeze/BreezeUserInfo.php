<?php

/**
 * BreezeUserInfo
 *
 * The purpose of this file is to generate a div with the user common info, name, avatar, post, icons, stuff like that.
 * @package Breeze mod
 * @version 1.0 Beta 3
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

if (!defined('SMF'))
	die('No direct access...');

	/* The purpose of this file is to centralize the user info,
	helps to avoid having to write code over and over again */
class BreezeUserInfo
{
	public static function profile($u)
	{
		global $txt, $context, $settings, $scripturl, $user_info, $memberContext, $context;

		/* Can't do much if the user id is empty... */
		if (empty($u))
			return;

		/* An extra check ;) */
		if (empty($memberContext[$u]))
			loadMemberContext($u);

		$user = $memberContext[$u];

		/* Set an empty array */
		$context['Breeze']['user_info'][$user['id']] = array(
			'facebox' => '',
			'link' => '',
			'data' => '',
			'name' => ''
		);

		/* Sometimes we just want the link */
		if (!empty($user['link']))
			$context['Breeze']['user_info'][$user['id']]['link'] = $user['link'];

		/* ...or the name */
		if (!empty($user['name']))
			$context['Breeze']['user_info'][$user['id']]['name'] = $user['name'];

		/* It all starts with the user's avatar or username... */
		$context['Breeze']['user_info'][$user['id']]['facebox'] .= '<a href="#facebox_'. $user['id'] .'" rel="facebox">'.(!empty($user['avatar']['href']) ? '<img src="'.$user['avatar']['href'].'" width="50px" />' : $user['link']) .'</a>';

		/* Set the data */
		$context['Breeze']['user_info'][$user['id']]['data'] = '
		<div id="facebox_'. $user['id'] .'" style="display:none;">
			<div class="windowbg">
				<span class="topslice">
					<span></span>
				</span>
				<div style="margin:3px;padding-right:15px;padding-left:5px;float:left;min-height:100px;">
					'.($user['avatar']['image'] ? $user['avatar']['image'] : '').'<br />'. $user['link'];

		$context['Breeze']['user_info'][$user['id']]['data'] .= '</div>
				<div>
					<ul class="breeze_user_left_info">';

		// Show the member's primary group (like 'Administrator') if they have one.
		if (!empty($user['group']))
			$context['Breeze']['user_info'][$user['id']]['data'] .= '<li class="membergroup"><span style="color:'.$user['group_color'].';">'. $user['group']. '</span></li>';

		// Show how many posts they have made.
		if (!isset($context['disabled_fields']['posts']))
			$context['Breeze']['user_info'][$user['id']]['data'] .= '<li class="postcount">'. $txt['member_postcount']. ': '. $user['posts']. '</li>';

		// Don't show these things for guests.
		if (!$user['is_guest'])
		{

			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $user['group'] == '') && $user['post_group'] != '')
				$context['Breeze']['user_info'][$user['id']]['data'] .= '<li class="postgroup">'. $user['post_group']. '</li>';

			$context['Breeze']['user_info'][$user['id']]['data'] .= '<li class="stars">'. $user['group_stars']. '</li>';

			// Show the member's gender icon?
			if (!empty($settings['show_gender']) && $user['gender']['image'] != '' && !isset($context['disabled_fields']['gender']))
				$context['Breeze']['user_info'][$user['id']]['data'] .= '<li class="gender">'. $txt['gender']. ': '. $user['gender']['image']. '</li>';

			// Show their personal text?
			if (!empty($settings['show_blurb']) && $user['blurb'] != '')
				$context['Breeze']['user_info'][$user['id']]['data'] .= '<li class="blurb">'. $user['blurb']. '</li>';

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
						$context['Breeze']['user_info'][$user['id']]['data'] .= '<li class="im_icons">
									<ul>';
					}
					$context['Breeze']['user_info'][$user['id']]['data'] .= '<li>'. $custom['value']. '</li>';
				}
				if ($shown)
					$context['Breeze']['user_info'][$user['id']]['data'] .= '</ul>
								</li>';
			}

			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{
				$context['Breeze']['user_info'][$user['id']]['data'] .= '<li class="profile">
									<ul>';

				// Don't show an icon if they haven't specified a website.
				if ($user['website']['url'] != '' && !isset($context['disabled_fields']['website']))
					$context['Breeze']['user_info'][$user['id']]['data'] .= '<li><a href="'. $user['website']['url']. '" title="' . $user['website']['title'] . '" target="_blank" class="new_win">'. ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $user['website']['title'] . '" />' : $txt['www']). '</a></li>';

				// Don't show the email address if they want it hidden.
				if (in_array($user['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
					$context['Breeze']['user_info'][$user['id']]['data'] .= '<li><a href="'. $scripturl . '?action=emailuser;sa=email;msg='. $user['id']. '" rel="nofollow">'. ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']). '</a></li>';

				$context['Breeze']['user_info'][$user['id']]['data'] .= '</ul>
				</li>';
			}

			// Any custom fields for standard placement?
			if (!empty($user['custom_fields']))
			{
				foreach ($user['custom_fields'] as $custom)
					if (empty($custom['placement']) || empty($custom['value']))
						$context['Breeze']['user_info'][$user['id']]['data'] .= '
								<li class="custom">'. $custom['title'] . ': '. $custom['value'] . '</li>';
			}
		}

		// Otherwise, show the guest's email.
		elseif (!empty($user['email']) && in_array($user['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
			$context['Breeze']['user_info'][$user['id']]['data'] .= '<li class="email"><a href="'. $scripturl . '?action=emailuser;sa=email;msg='. $context['id'] . '" rel="nofollow">'. ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']). '</a></li>';



		/* Info list end */
		$context['Breeze']['user_info'][$user['id']]['data'] .= '</ul>
				</div>
				<div class="clear"></div>
			<span class="botslice"><span></span></span>
			</div>
		</div>';
	}
}