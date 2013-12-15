<?php

/**
 * BreezeParser
 *
 * The purpose of this file is to identify something in a tezt string and convert that to something different, for example, a url into an actual html link.
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
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

class BreezeParser
{
	protected $regex;

	function __construct($settings, $tools)
	{
		$this->settings = $settings;
		$this->tools = $tools;

		// Regex stuff
		$this->regex = array(
			'mention' => '~@\(([\s\w,;-_\[\]\\\/\+\.\~\$\!]+), ([0-9]+)\)~u',
		);
	}

	public function display($string)
	{
		if (empty($string))
			return false;

		$this->s = $string;
		$temp = $this->tools->remove(get_class_methods(__CLASS__), array('__construct', 'display'), false);

		// We may want to add something before it gets parsed...
		call_integration_hook('integrate_breeze_before_parser', array(&$this->s));

		foreach ($temp as $t)
			$this->$t();

		// ...or after?
		call_integration_hook('integrate_breeze_after_parser', array(&$this->s));

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
		}
	}

	protected function smf_parse()
	{
		$this->s = parse_bbc($this->s);
	}
}
