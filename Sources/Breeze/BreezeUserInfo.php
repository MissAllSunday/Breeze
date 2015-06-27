<?php

/**
 * BreezeUserInfo
 *
 * @package Breeze mod
 * @version 1.0.8
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeUserInfo
{
	public static function profile($u)
	{
		global $txt, $context, $settings, $scripturl, $user_info, $memberContext, $context;

		// Can't do much if the user id is empty...
		if (empty($u))
			return;

		// An extra check ;)
		if (empty($memberContext[$u]))
			loadMemberContext($u);

		$user = $memberContext[$u];

		// Set an empty array
		$context['Breeze']['user_info'][$user['id']] = array(
			'facebox' => '',
			'link' => '',
			'data' => '',
			'name' => ''
		);

		// The user must exists!
		if (!isset($user) || empty($user))
			return false;

		// Sometimes we just want the link
		if (!empty($user['link']))
			$context['Breeze']['user_info'][$user['id']]['link'] = $user['link'];

		// ...or the name
		if (!empty($user['name']))
			$context['Breeze']['user_info'][$user['id']]['name'] = $user['name'];

		// It all starts with the user's avatar or username...
		$context['Breeze']['user_info'][$user['id']]['facebox'] .= '<a href="#facebox_'. $user['id'] .'" rel="facebox"><img src="'.(!empty($user['avatar']['href']) ? ''.$user['avatar']['href'].'' : $settings['default_theme_url'] .'/images/breeze/default_user.png') . '" width="50px" /></a>';

		// Set the data
		$context['Breeze']['user_info'][$user['id']]['data'] = '
		<div id="facebox_'. $user['id'] .'" style="display:none;">
			<div class="description">
				<div style="margin:3px;padding-right:15px;padding-left:5px;float:left;min-height:100px;">
					'.($user['avatar']['image'] ? $user['avatar']['image'] : '').'<br />'. $user['link'];

		$context['Breeze']['user_info'][$user['id']]['data'] .= '
				</div>
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
						$context['Breeze']['user_info'][$user['id']]['data'] .= '
								<li class="im_icons">
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
				$context['Breeze']['user_info'][$user['id']]['data'] .= '
								<li class="profile">
									<ul>';

				// Don't show an icon if they haven't specified a website.
				if ($user['website']['url'] != '' && !isset($context['disabled_fields']['website']))
					$context['Breeze']['user_info'][$user['id']]['data'] .= '<li><a href="'. $user['website']['url']. '" title="' . $user['website']['title'] . '" target="_blank" class="new_win">'. ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $user['website']['title'] . '" />' : $txt['www']). '</a></li>';

				// Don't show the email address if they want it hidden.
				if (in_array($user['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
					$context['Breeze']['user_info'][$user['id']]['data'] .= '<li><a href="'. $scripturl . '?action=emailuser;sa=email;msg='. $user['id']. '" rel="nofollow">'. ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']). '</a></li>';

				$context['Breeze']['user_info'][$user['id']]['data'] .= '
									</ul>
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



		// Info list end
		$context['Breeze']['user_info'][$user['id']]['data'] .= '</ul>
				</div>
				<div class="clear"></div>
			</div>
		</div>';
	}

	// Poster is a guest
	public static function guest($u)
	{
		global $txt, $context;

		// Guest don't have that many options...
		$context['Breeze']['user_info'][$u] = array(
			'facebox' => $txt['guest_title'],
			'link' => $txt['guest_title'],
			'data' => '',
			'name' => $txt['guest_title']
		);
	}
}
