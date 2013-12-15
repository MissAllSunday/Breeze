<?php

/**
 * BreezeTools
 *
 * The purpose of this file is to provide some tools used across the mod
 * @package Breeze mod
 * @version 1.0
 * @author Jessica Gonz�lez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica Gonz�lez
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
	 * A helper method to load JavaScript code for the profile and wall page.
	 * @see BreezeTools
	 * @return void
	 */
	public function profileHeaders($userSettings)
	{
		global $context, $settings, $user_info;
		static $profile_header = false;

		$breezeGlobals = Breeze::sGlobals('get');

		if (!$profile_header)
		{

			// DUH! winning!
			if ($this->settings->enable('admin_settings_enable') && ($breezeGlobals->getValue('action') == 'profile' || $breezeGlobals->getValue('action') == 'wall'))
				$context['insert_after_template'] .= Breeze::who(true);

			// Generic JS vars and files
			$context['insert_after_template'] .= '
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.caret.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.atwho.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breezeTabs.js"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
		var breeze_current_user = '. JavaScriptEscape($user_info['id']) .';
		var breeze_how_many_mentions = '. (JavaScriptEscape(!empty($userSettings['how_many_mentions']) ? $userSettings['how_many_mentions'] : 5)) .';
		var breeze_session_id = ' . JavaScriptEscape($context['session_id']) . ';
		var breeze_session_var = ' . JavaScriptEscape($context['session_var']) . ';
		var breeze_loadMore = '. JavaScriptEscape($this->text->getText('general_load_more')) .';
		var breeze_loadMore_no = '. JavaScriptEscape($this->text->getText('general_load_more_no')) .';
	// ]]></script>';

			// Does the user wants to use infinite scroll?
			if (!empty($userSettings['load_more']))
				$context['insert_after_template'] .= '
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breezeScroll.js"></script>';

			$profile_header = true;
		}
	}
}
