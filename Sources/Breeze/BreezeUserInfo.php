<?php

/**
 * BreezeUserInfo
 *
 * The purpose of this file is to generate a div with the user common info, name, avatar, post, icons, stuff like that.
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

if (!defined('SMF'))
	die('No direct access...');

	/* The purpose of this file is to centralize the user info,
	helps to avoid having to write code over and over again */
class BreezeUserInfo
{
	public static function profile($u)
	{
		global $memberContext, $context;

		// Can't do much if the user id is empty...
		if (empty($u))
			return;

		// An extra check ;)
		if (empty($memberContext[$u]))
			loadMemberContext($u);

		// Pass the user data to the template
		$context['user']['info'] = $memberContext[$u];

		loadtemplate(Breeze::$name .'Display');

		// Call the right func and strip the layers
		$context['template_layers'] = array();
		$context['sub_template'] = 'userInfo';

		// Set an empty array to avoid undefined errors when an user no longer exists
		$context['Breeze']['user_info'][$user['id']] = array(
			'facebox' => '',
			'link' => '',
			'data' => '',
			'name' => ''
		);

		// The user must exists!
		if (!isset($user) || empty($user))
			return false;

		else
			template_userInfo();
	}
}
