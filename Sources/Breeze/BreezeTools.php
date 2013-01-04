<?php

/**
 * BreezeTools
 *
 * The purpose of this file is to provide some tools used across the mod
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
	die('No direct access...');

class BreezeTools
{
	function __construct($settings, $text)
	{
		$this->text = $text;
		$this->settings = $settings;

		/* Get globals */
		$this->_data = new BreezeGlobals('request');
	}

	/* @todo move this to the buffer hook, I don't trust $context['html_headers'] anymore */
	public function headers($type = 'profile')
	{
		global $context, $settings, $user_info;
		static $header_done = false;

		if (!$header_done)
		{
			$context['html_headers'] .= '
			<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery.min.js"%3E%3C/script%3E\'))</script>
			<link href="'. $settings['default_theme_url'] .'/css/breeze.css" rel="stylesheet" type="text/css" />';

			$header_done = true;
		}

		/* Define some variables for the ajax stuff */
		if ($type == 'profile')
		{
			$context['html_headers'] .= '
			<script type="text/javascript"><!-- // --><![CDATA[
				var breeze_error_message = "'. $this->text->getText('error_message') .'";
				var breeze_success_message = "'. $this->text->getText('success_message') .'";
				var breeze_empty_message = "'. $this->text->getText('empty_message') .'";
				var breeze_error_delete = "'. $this->text->getText('error_message') .'";
				var breeze_success_delete = "'. $this->text->getText('success_delete') .'";
				var breeze_confirm_delete = "'. $this->text->getText('confirm_delete') .'";
				var breeze_confirm_yes = "'. $this->text->getText('confirm_yes') .'";
				var breeze_confirm_cancel = "'. $this->text->getText('confirm_cancel') .'";
				var breeze_already_deleted = "'. $this->text->getText('already_deleted') .'";
				var breeze_cannot_postStatus = "'. $this->text->getText('cannot_postStatus') .'";
				var breeze_cannot_postComments = "'. $this->text->getText('cannot_postComments') .'";
				var breeze_page_loading = "'. $this->text->getText('page_loading') .'";
				var breeze_page_loading_end = "'. $this->text->getText('page_loading_end') .'";
		// ]]></script>';

			/* Let's load jquery from CDN only if it hasn't been loaded yet */
			$context['html_headers'] .= '
			<link href="'. $settings['default_theme_url'] .'/css/facebox.css" rel="stylesheet" type="text/css" />
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/facebox.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/livequery.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/jquery.noty.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/top.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/center.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/topCenter.js"></script>';

			/* Does the user wants to use infinite scroll? */
			if (!empty($context['member']['options']['Breeze_infinite_scroll']))
				$context['html_headers'] .= '
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.infinitescroll.min.js" type="text/javascript"></script>';

			/* Load breeze.js untill everyone else is loaded */
			$context['html_headers'] .= '
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breeze.js"></script>';
		}

		/* Does the admin wants to add more actions? */
		if ($this->settings->enable('allowedActions'))
			parent::$_allowedActions = array_merge(parent::$_allowedActions, explode(',', $this->settings->getSetting('allowedActions')));

		/* Stuff for the notifications, don't show this if we aren't on a specified action */
		if ($type == 'noti' && empty($user_info['is_guest']) && (in_array($this->_data->getValue('action'), parent::$_allowedActions) || $this->_data->getValue('action') == false))
		{
			$notifications = $this->notifications();

			$context['insert_after_template'] .= '
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/jquery.noty.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/top.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/topLeft.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/layouts/topRight.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/noty/themes/default.js"></script>
			<script type="text/javascript"><!-- // --><![CDATA[
				var breeze_error_message = "'. $this->text->getText('error_message') .'";
				var breeze_noti_markasread = "'. $this->text->getText('noti_markasread') .'";
				var breeze_noti_markasread_after = "'. $this->text->getText('noti_markasread_after') .'";
				var breeze_noti_delete = "'. $this->text->getText('general_delete') .'";
				var breeze_noti_delete_after = "'. $this->text->getText('noti_delete_after') .'";
				var breeze_noti_close = "'. $this->text->getText('noti_close') .'";
				var breeze_noti_cancel = "'. $this->text->getText('confirm_cancel') .'";
			// ]]></script>';

			$context['insert_after_template'] .= $notifications->doStream($user_info['id']);
		}

		/* Admin bits */
		if($type == 'admin')
			$context['html_headers'] .= '
			<script src="'. $settings['default_theme_url'] .'/js/jquery.zrssfeed.js" type="text/javascript"></script>
			<script type="text/javascript">
var breeze_feed_error_message = "'. $this->text->getText('feed_error_message') .'";

$(document).ready(function ()
{
	$(\'#breezelive\').rssfeed(\''. Breeze::$supportStite .'\',
	{
		limit: 5,
		header: false,
		date: true,
		linktarget: \'_blank\',
		errormsg: breeze_feed_error_message
   });
});
 </script>
';

	}

	/* Relative dates  http://www.zachstronaut.com/posts/2009/01/20/php-relative-date-time-string.html */
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

	/* A function to cut-off a string */
	public function truncateString($string, $limit, $break = ' ', $pad = '...')
	{
		if(empty($limit))
			$limit = 30;

		 /* return with no change if string is shorter than $limit */
		if(strlen($string) <= $limit)
			return $string;

		/* is $break present between $limit and the end of the string? */
		if(false !== ($breakpoint = strpos($string, $break, $limit)))
			if($breakpoint < strlen($string) - 1)
				$string = substr($string, 0, $breakpoint) . $pad;

		return $string;
	}

	/* Checks if a value on a multidimencional array exists and return the main key */
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

	public function loadUserInfo($id)
	{
		global $memberContext;

		/* If this isn't an array, lets change it to one */
		if (!is_array($id))
			$id = array($id);

		/* SMF always return the data as an array */
		$array = loadMemberData($id, false, 'profile');

		/* Load the users data if it wasn't loaded already */
		if (!empty($array) && is_array($array))
			foreach ($array as $u)
			{
				if (empty($memberContext[$u]))
					loadMemberContext($u);

				/* Create the context var */
				BreezeUserInfo::profile($u);
			}

		else
			return false;
	}
}