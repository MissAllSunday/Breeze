<?php

/**
 * BreezeUserInfo
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica GonzÃ¡lez <suki@missallsunday.com>
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
// var_dump($user);die;
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
				<div>';

		// Info list end
		$context['Breeze']['user_info'][$user['id']]['data'] .= '
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
