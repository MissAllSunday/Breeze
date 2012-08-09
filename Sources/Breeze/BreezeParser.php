<?php

/**
 * BreezeParser
 *
 * The purpose of this file is to identify something in a tezt string and convert that to something different, for example, a url into an actual html link.
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
	die('Hacking attempt...');

class BreezeParser
{
	private $notification;

	function __construct()
	{
		$this->notification = Breeze::notifications();
		$this->settings = Breeze::settings();
		$this->tools = Breeze::tools();

		/* Regex stuff */
		$this->regex = array(
			'url' => '~(?<=[\s>\.(;\'"]|^)((?:http|https)://[\w\-_%@:|]+(?:\.[\w\-_%]+)*(?::\d+)?(?:/[\w\-_\~%\.@!,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i',
			'mention' => '~{(?<id>[0-9]+),(?<name>[\s\w,;-_\[\]\\\/\+\.\~\$\!]+),(?<display>[\s\w,;-_\[\]\\\/\+\.\~\$\!]+)}~u',
		);
	}

	public function display($string)
	{
		$this->s = $string;
		$temp = get_class_methods('BreezeParser');
		$temp = BreezeTools::remove($temp, array('__construct', 'display'), false);

		foreach ($temp as $t)
			$this->s = $this->$t($this->s);

		return $this->s;
	}

	/* Convert any valid urls on to links */
	private function urltoLink($s)
	{
		if (preg_match_all($this->regex['url'], $s, $matches))
			foreach($matches[0] as $m)
				$s = str_replace($m, '<a href="'.$m.'" class="bbc_link" target="_blank">'.$m.'</a>', $s);

		return $s;
	}

	private function mention($s)
	{
		global $scripturl;

		/* Search for all possible names */
		if (preg_match_all($this->regex['mention'], $s, $matches, PREG_SET_ORDER))
		{
			/* Find any instances */
			foreach ($matches as $query)
			{
				$find[] = $query[0];

				$replace[] = '@<a href="' . $scripturl . '?action=profile;u=' . $query['id'] . '" class="bbc_link" target="_blank">' . $query['display'] . '</a>';
			}

			/* Do the replacement already */
			$s = str_replace($find, $replace, $s);

			/* We are done mutilating the string, lets returning it */
			return $s;
		}

		/* Nothing was found */
		else
			return $s;
	}
}