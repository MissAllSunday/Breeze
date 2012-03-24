<?php

/**
 * Breeze_
 *
 * The purpose of this file is to identify something in a tezt string and convert that to something different, for example, a url into an actual html link.
 * @package Breeze mod
 * @version 1.0 Beta 2
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
	die('Hacking attempt...');

class Breeze_Parser
{
	private $notification;

	function __construct()
	{
		Breeze::Load(array(
			'Subs',
			'Notifications',
			'UserInfo'
		));

		$this->notification = new Breeze_Notifications();
		$this->settings = Breeze_Settings::getInstance();

		/* Regex stuff */
		$this->regex = array(
			'url' => '~(?<=[\s>\.(;\'"]|^)((?:http|https)://[\w\-_%@:|]+(?:\.[\w\-_%]+)*(?::\d+)?(?:/[\w\-_\~%\.@!,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i',
			'mention' => '/{([^<>&"\'=\\\\]*)}/'
		);
	}

	public function Display($string, $mention_info = false)
	{
		$this->s = $string;
		$temp = get_class_methods('Breeze_Parser');
		$temp = Breeze_Subs::Remove($temp, array('__construct', 'Display'), false);

		/* Used to notify the user */
		if ($mention_info)
			$this->mention_info = array(
				$mention_info['wall_owner'],
				$mention_info['wall_poster']
			);

		foreach ($temp as $t)
			$this->s = $this->$t($this->s);

		return $this->s;
	}

	/* Convert any valid urls on to links */
	private function UrltoLink($s)
	{
		if (preg_match_all($this->regex['url'], $s, $matches))
			foreach($matches[0] as $m)
				$s = str_replace($m, '<a href="'.$m.'" class="bbc_link" target="_blank">'.$m.'</a>', $s);

		return $s;
	}

	private function Mention($s)
	{
		global $memberContext, $context, $user_info, $scripturl;

		if (preg_match_all($this->regex['mention'], $s, $matches))
			foreach($matches[1] as $m)
			{
				if (in_array($m, array('_', '|')) || preg_match('~[<>&"\'=\\\\]~', preg_replace('~&#(?:\\d{1,7}|x[0-9a-fA-F]{1,6});~', '', $m)) != 0 || strpos($m, '[code') !== false || strpos($m, '[/code') !== false)
					$s = str_replace($matches[0], '@'.$m, $s);

				/* We need to do this since we only have the name, not the id */
				if ($user = loadMemberData($m, true, 'minimal'))
				{
					$context['Breeze']['user_info'][$user[0]] = Breeze_UserInfo::Profile($user[0], true);
					$s = str_replace($matches[0], '@'.$context['Breeze']['user_info']['link'][$user[0]], $s);

					/* Does this user wants to be notificated? */
					if ($user[0] != $user_info['id'])
					{
						/* Load all the members up. */
						$temp_users_load = Breeze_Subs::LoadUserInfo($this->mention_info);

						/* Build the params */
						$params = array(
							'user' => $user[0],
							'type' => 'mention',
							'time' => time(),
							'read' => 0,
							'content' => array(
								'message' => $this->mention_info[1] == $this->mention_info[0] ? sprintf($this->settings->GetText('mention_message_own_wall'), $temp_users_load[$this->mention_info[1]]['link']) : sprintf($this->settings->GetText('mention_message'), $temp_users_load[$this->mention_info[1]]['link'], $temp_users_load[$this->mention_info[0]]['link']),
								'url' => $scripturl .'?action=profile;area=breezenoti;u='. $user[0],
								'from_link' => $temp_users_load[$this->mention_info[1]]['link'],
								'from_id' => $temp_users_load[$this->mention_info[1]]['id'],
							)
						);

						/* Create the notification */
						$this->notification->Create($params);
					}
				}
				else
					$s = str_replace($matches[0], '@'.$m, $s);
			}

		return $s;
	}
}