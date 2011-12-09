<?php

/**
 * Breeze_
 * 
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0
 * @author Jessica Gonzalez <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica Gonzalez
 * @license http://mozilla.org/MPL/2.0/
 */

/**
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License version 2.0 (the \License"). You can obtain a copy of the
 * License at http://mozilla.org/MPL/2.0/.
 */


if (!defined('SMF'))
	die('Hacking attempt...');

class Breeze_Parser
{
	function __construct($string)
	{
		$this->s = $string;
	}

	function Display()
	{
		$temp = get_class_methods('Breeze_Parser');
		$temp = self::remove($temp, '__construct', false);
		$temp = self::remove($temp, 'Display', false);
		$temp = self::remove($temp, 'remove', false);

		foreach ($temp as $t)
			$this->s = self::$t($this->s);

		return $this->s;
	}

	/* Convert any valid urls on to links*/
	private static function UrltoLink($s)
	{
		$url_regex = '~(?<=[\s>\.(;\'"]|^)((?:http|https)://[\w\-_%@:|]+(?:\.[\w\-_%]+)*(?::\d+)?(?:/[\w\-_\~%\.@!,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i';

		if (preg_match_all($url_regex, $s, $matches))
			foreach($matches[0] as $m)
				$s = str_replace($m, '<a href="'.$m.'" class="bbc_link" target="_blank">'.$m.'</a>', $s);

		return $s;
	}
}