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
			'Notifications'
		));

		$this->notification = new Breeze_Notifications();
	}

	function Display($string)
	{
		$this->s = $string;
		$temp = get_class_methods('Breeze_Parser');
		$temp = Breeze_Subs::Remove($temp, array('__construct', 'Display'), false);

		foreach ($temp as $t)
			$this->s = $this->$t($this->s);

		return $this->s;
	}

	/* Convert any valid urls on to links*/
	private function UrltoLink($s)
	{
		/* regex "Borrowed" from Sources/Subs.php:parse_bbc() */
		$url_regex = '~(?<=[\s>\.(;\'"]|^)((?:http|https)://[\w\-_%@:|]+(?:\.[\w\-_%]+)*(?::\d+)?(?:/[\w\-_\~%\.@!,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i';

		if (preg_match_all($url_regex, $s, $matches))
			foreach($matches[0] as $m)
				$s = str_replace($m, '<a href="'.$m.'" class="bbc_link" target="_blank">'.$m.'</a>', $s);

		return $s;
	}

	private function Mention($s)
	{
		global $memberContext, $context;

		$regex = '/{([^<>&"\'=\\\\]*)}/';

		if (preg_match_all($regex, $s, $matches))
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
					/* Some if check here */
					{
						/* Build the params */
						$params = array(
							/* Params here */
						);

						/* Create the notification */
						$this->notification->Create('mention', $params);
					}

				}
				else
					$s = str_replace($matches[0], '@'.$m, $s);
			}

		return $s;
	}
}