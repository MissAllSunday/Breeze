<?php

/**
 * BreezeParser
 *
 * The purpose of this file is to identify something in a tezt string and convert that to something different, for example, a url into an actual html link.
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica Gonz�lez <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica Gonz�lez
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
 * Jessica Gonz�lez.
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */


if (!defined('SMF'))
	die('No direct access...');

class BreezeParser
{
	private $notification;

	function __construct($settings, $tools)
	{
		$this->settings = $settings;
		$this->tools = $tools;

		// Regex stuff
		$this->regex = array(
			'url' => '~(?<=[\s>\.(;\'"]|^)((?:http|https)://[\w\-_%@:|]+(?:\.[\w\-_%]+)*(?::\d+)?(?:/[\w\-_\~%\.@!,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i',
			'mention' => '~@\(([\s\w,;-_\[\]\\\/\+\.\~\$\!]+), ([0-9]+)\)~u',
		);
	}

	public function display($string)
	{
		if (empty($string))
			return false;

		$this->s = $string;
		$temp = get_class_methods('BreezeParser');
		$temp = $this->tools->remove($temp, array('__construct', 'display'), false);

		foreach ($temp as $t)
			$this->$t();

		return $this->s;
	}

	// Convert any valid urls on to links
	protected function urltoLink()
	{
		if (preg_match_all($this->regex['url'], $this->s, $matches))
			foreach($matches[0] as $m)
				$this->s = str_replace($m, '<a href="'.$m.'" class="bbc_link" target="_blank">'.$m.'</a>', $this->s);

		return $this->s;
	}

	protected function mention()
	{
		global $scripturl;

		// Search for all possible names
		if (preg_match_all($this->regex['mention'], $this->s, $matches, PREG_SET_ORDER))
		{
			// Find any instances
			foreach ($matches as $query)
			{
				$find[] = $query[0];

				$replace[] = '@<a href="' . $scripturl . '?action=profile;u=' . $query[2] . '" class="bbc_link" target="_blank">' . $query[1] . '</a>';
			}

			// Do the replacement already
			$this->s = str_replace($find, $replace, $this->s);

			// We are done mutilating the string, lets returning it
			return $this->s;
		}

		// Nothing was found
		else
			return $this->s;
	}

	protected function smf_parse()
	{
		return parse_bbc();
	}
}
