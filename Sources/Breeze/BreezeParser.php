<?php

/**
 * BreezeParser
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica Gonzalez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeParser
{
	protected $regex;

	function __construct($tools)
	{
		$this->tools = $tools;

		// Regex stuff
		$this->regex = array(
			'mention' => '~\(([\s\w,;-_\[\]\\\/\+\.\~\$\!]+), ([0-9]+)\)~u',
		);
	}

	/**
	 * BreezeParser::display()
	 *
	 * Gets a string, calls all methods and returns the modified version of it.
	 * @param string the text to parse
	 * @return the parsed text
	 */
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

		return un_htmlspecialchars($this->s);
	}

	/**
	 * BreezeParser::mention()
	 *
	 * Scans the text looking for valid mentions, the regex checks for (username, ID)
	 */
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

	/**
	 * BreezeParser::smf_parser()
	 *
	 * Calls and executes Subs::parse_bbc()
	 */
	protected function smf_parse()
	{
		if (!$this->tools->enable('parseBBC'))
			return;

		$this->s = parse_bbc($this->s);
	}
}
