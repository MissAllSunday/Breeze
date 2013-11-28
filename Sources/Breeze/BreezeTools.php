<?php

/**
 * BreezeTools
 *
 * The purpose of this file is to provide some tools used across the mod
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <suki@missallsunday.com>
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

class BreezeTools
{
	function __construct($settings, $text)
	{
		$this->text = $text;
		$this->settings = $settings;

		// Get globals
		$this->_data = new BreezeGlobals('request');
	}

	// Relative dates  http://www.zachstronaut.com/posts/2009/01/20/php-relative-date-time-string.html
	public function timeElapsed($ptime)
	{
		$etime = time() - $ptime;

		if ($etime < 1)
			return $this->text->getText('time_just_now');

		$a = array(
			12 * 30 * 24 * 60 * 60	=> $this->text->getText('time_year'),
			30 * 24 * 60 * 60		=> $this->text->getText('time_month'),
			24 * 60 * 60			=> $this->text->getText('time_day'),
			60 * 60					=> $this->text->getText('time_hour'),
			60						=> $this->text->getText('time_minute'),
			1						=> $this->text->getText('time_second')
		);

		foreach ($a as $secs => $str)
		{
			$d = $etime / $secs;
			if ($d >= 1)
			{
				$r = round($d);
				return $r . ' ' . $str . ($r > 1 ? 's ' : ' '). $this->text->getText('time_ago');
			}
		}
	}

	public function isJson($string)
	{
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	// Blatantly stolen from Sources/Subs.php::constructPageIndex()
	public function pagination($base_url, &$start, $max_value, $num_per_page, $flexible_start = false)
	{
		global $modSettings;

		// Save whether $start was less than 0 or not.
		$start = (int) $start;
		$start_invalid = $start < 0;

		// Make sure $start is a proper variable - not less than 0.
		if ($start_invalid)
			$start = 0;
		// Not greater than the upper bound.
		elseif ($start >= $max_value)
			$start = max(0, (int) $max_value - (((int) $max_value % (int) $num_per_page) == 0 ? $num_per_page : ((int) $max_value % (int) $num_per_page)));
		// And it has to be a multiple of $num_per_page!
		else
			$start = max(0, (int) $start - ((int) $start % (int) $num_per_page));

		// Wireless will need the protocol on the URL somewhere.
		if (WIRELESS)
			$base_url .= ';' . WIRELESS_PROTOCOL;

		$base_link = '<a class="navPages" href="' . ($flexible_start ? $base_url : strtr($base_url, array('%' => '%%')) . ';start=%1$d') . '">%2$s</a> ';

		// If they didn't enter an odd value, pretend they did.
		$PageContiguous = (int) ($modSettings['compactTopicPagesContiguous'] - ($modSettings['compactTopicPagesContiguous'] % 2)) / 2;

		// Show the first page. (>1< ... 6 7 [8] 9 10 ... 15)
		if ($start > $num_per_page * $PageContiguous)
			$pageindex = sprintf($base_link, 0, '1');
		else
			$pageindex = '';

		// Show the ... after the first page.  (1 >...< 6 7 [8] 9 10 ... 15)
		if ($start > $num_per_page * ($PageContiguous + 1))
			$pageindex .= '<span style="font-weight: bold;" onclick="' . htmlspecialchars('expandPages(this, ' . JavaScriptEscape(($flexible_start ? $base_url : strtr($base_url, array('%' => '%%')) . ';start=%1$d')) . ', ' . $num_per_page . ', ' . ($start - $num_per_page * $PageContiguous) . ', ' . $num_per_page . ');') . '" onmouseover="this.style.cursor = \'pointer\';"> ... </span>';

		// Show the pages before the current one. (1 ... >6 7< [8] 9 10 ... 15)
		for ($nCont = $PageContiguous; $nCont >= 1; $nCont--)
			if ($start >= $num_per_page * $nCont)
			{
				$tmpStart = $start - $num_per_page * $nCont;
				$pageindex.= sprintf($base_link, $tmpStart, $tmpStart / $num_per_page + 1);
			}

		// Show the current page. (1 ... 6 7 >[8]< 9 10 ... 15)
		if (!$start_invalid)
			$pageindex .= '[<strong>' . ($start / $num_per_page + 1) . '</strong>] ';
		else
			$pageindex .= sprintf($base_link, $start, $start / $num_per_page + 1);

		// Show the pages after the current one... (1 ... 6 7 [8] >9 10< ... 15)
		$tmpMaxPages = (int) (($max_value - 1) / $num_per_page) * $num_per_page;
		for ($nCont = 1; $nCont <= $PageContiguous; $nCont++)
			if ($start + $num_per_page * $nCont <= $tmpMaxPages)
			{
				$tmpStart = $start + $num_per_page * $nCont;
				$pageindex .= sprintf($base_link, $tmpStart, $tmpStart / $num_per_page + 1);
			}

		// Show the '...' part near the end. (1 ... 6 7 [8] 9 10 >...< 15)
		if ($start + $num_per_page * ($PageContiguous + 1) < $tmpMaxPages)
			$pageindex .= '<span style="font-weight: bold;" onclick="expandPages(this, \'' . ($flexible_start ? strtr($base_url, array('\'' => '\\\'')) : strtr($base_url, array('%' => '%%', '\'' => '\\\'')) . ';start=%1$d') . '\', ' . ($start + $num_per_page * ($PageContiguous + 1)) . ', ' . $tmpMaxPages . ', ' . $num_per_page . ');" onmouseover="this.style.cursor=\'pointer\';"> ... </span>';

		// Show the last number in the list. (1 ... 6 7 [8] 9 10 ... >15<)
		if ($start + $num_per_page * $PageContiguous < $tmpMaxPages)
			$pageindex .= sprintf($base_link, $tmpMaxPages, $tmpMaxPages / $num_per_page + 1);

		// The next batch
		$next = $start + $num_per_page;

		// Show a "next" item
		if ($next <= $max_value)
			$pageindex .= '<a class="navPagesNext" id="breeze_next" href="' . ($flexible_start ? $base_url : strtr($base_url, array('%' => '%%')) . ';start='. $next) . '">&#187;</a> ';


		return $pageindex;
	}

	// A function to cut-off a string
	public function truncateString($string, $limit, $break = ' ', $pad = '...')
	{
		if(empty($limit))
			$limit = 30;

		 // return with no change if string is shorter than $limit
		if(strlen($string) <= $limit)
			return $string;

		// is $break present between $limit and the end of the string?
		if(false !== ($breakpoint = strpos($string, $break, $limit)))
			if($breakpoint < strlen($string) - 1)
				$string = substr($string, 0, $breakpoint) . $pad;

		return $string;
	}

	// Checks if a value on a multidimensional array exists and return the main key
	public function returnKey($value, $array)
	{
		if (empty($value) || empty($array))
			return false;

		foreach ($array as $k => $v)
		{
			if (is_array($v))
			{
				if (in_array($value, $v))
					return $k;

					else
						return false;
			}

			else
			{
				if ($v == $value)
					return $k;

				else
					return false;
			}
		}
	}

	public function remove($array, $val, $preserve_keys = true)
	{
		if (empty($array) || empty($val) || !is_array($array))
			return false;

		if (!is_array($val))
		{
			if (!in_array($val, $array))
				return $array;

			foreach($array as $key => $value)
			{
				if ($value == $val)
					unset($array[$key]);
			}
		}

		elseif (is_array($val))
		{
			foreach($val as $find)
				foreach($array as $key => $value)
				{
					if (empty($array) || !is_array($array))
						return false;

					if ($value == $find)
						unset($array[$key]);
				}
		}

		else
			return false;

		return ($preserve_keys === true) ? $array : array_values($array);
	}

	public function loadUserInfo($id, $returnID = false)
	{
		global $memberContext;

		// If this isn't an array, lets change it to one
		if (!is_array($id))
			$id = array($id);

		// $memberContext gets set and globalized, we're gonna take advantage of it
		$loaded_ids = loadMemberData($id, false, 'profile');

		// Set the context var
		foreach ($id as $u)
		{
			// Avoid SMF showing an awful error message
			if (is_array($loaded_ids) && in_array($u, $loaded_ids))
			{
				loadMemberContext($u);

				// Normal context var
				BreezeUserInfo::profile($u);
			}

			// Poster is a guest
			else
				BreezeUserInfo::guest($u);
		}

		// Lastly, if the ID was requested, sent it back!
		if ($returnID)
			return $loaded_ids;
	}

	/**
	 * Breeze::profileHeaders()
	 *
	 * Static method, a helper method to load JavaScript code for the profile and wall page
	 * @see BreezeTools
	 * @return void
	 */
	public function profileHeaders()
	{
		global $context, $settings, $user_info;
		static $profile_header = false;

		if (empty($breezeController))
			$breezeController = new BreezeController();

		$breezeGlobals = Breeze::sGlobals('get');

		if (!$profile_header)
		{
			// Get this user's settings.
			$context['member']['breezeOptions'] = $query->getUserSettings($context['member']['id']);

			$context['html_headers'] .= '
			<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery-1.9.1.min.js"%3E%3C/script%3E\'))</script>
			<link href="'. $settings['default_theme_url'] .'/css/breeze.css" rel="stylesheet" type="text/css" />';

			// DUH! winning!
			if ($this->settings->enable('admin_settings_enable') && ($breezeGlobals->getValue('action') == 'profile' || $breezeGlobals->getValue('action') == 'wall'))
				$context['insert_after_template'] .= Breeze::who(true);

			// Let's load jquery from CDN only if it hasn't been loaded yet
			$context['html_headers'] .= '
			<link href="'. $settings['default_theme_url'] .'/css/facebox.css" rel="stylesheet" type="text/css" />
			<link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url'] .'/css/jquery.atwho.css"/>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/facebox.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.caret.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.atwho.js"></script>';

			// Generic JS vars and files
			$context['insert_after_template'] .= '
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.hashchange.min.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breezeTabs.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/jquery.noty.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/top.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/topLeft.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/topRight.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/themes/default.js"></script>
			<script type="text/javascript"><!-- // --><![CDATA[
				var breeze_current_user = '. JavaScriptEscape($user_info['id']) .';
				var breeze_how_many_mentions = '. (JavaScriptEscape(!empty($context['member']['options']['Breeze_how_many_mentions']) ? $context['member']['options']['Breeze_how_many_mentions'] : 5)) .';
				var breeze_session_id = ' . JavaScriptEscape($context['session_id']) . ';
				var breeze_session_var = ' . JavaScriptEscape($context['session_var']) . ';
			// ]]></script>';

			// Does the user wants to use infinite scroll?
			if (!empty($context['member']['options']['Breeze_infinite_scroll']))
				$context['insert_after_template'] .= '
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breezeScroll.js"></script>';

			// Load breeze.js until everyone else is loaded
			$context['html_headers'] .= '
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breeze.js"></script>';

			$profile_header = true;
		}
	}
}
