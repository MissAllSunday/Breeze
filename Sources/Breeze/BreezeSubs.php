<?php

/**
 * BreezeSubs
 *
 * The purpose of this file is to provide some tools used across the mod
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

class BreezeSubs
{
	function __construct(){}

	/* @todo move this to the buffer hook, I don't trust $context['html_headers'] anymore */
	public function headersNotification()
	{
		global $context, $settings;

		/* Some check here */
		$context['html_headers'] .= '
			<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery.min.js"%3E%3C/script%3E\'))</script>
			<script src="'. $settings['default_theme_url'] .'/js/sticky.full.js" type="text/javascript"></script>
			<script type="text/javascript"><!-- // --><![CDATA[
				var breeze_user_noti_position = "top-right";
			// ]]></script>
			<link rel="stylesheet" href="'. $settings['default_theme_url'] .'/css/sticky.full.css" type="text/css" />';
	}

	/* @todo move this to the buffer hook, I don't trust $context['html_headers'] anymore */
	public function Headers($admin = false)
	{
		global $context, $settings;

		$text = BreezeSettings::getInstance();

		/* Define some variables for the ajax stuff */
		$context['html_headers'] .= '
		<script type="text/javascript"><!-- // --><![CDATA[
			var breeze_error_message = "'. $text->GetText('error_message') .'";
			var breeze_success_message = "'. $text->GetText('success_message') .'";
			var breeze_empty_message = "'. $text->GetText('empty_message') .'";
			var breeze_error_delete = "'. $text->GetText('error_message') .'";
			var breeze_success_delete = "'. $text->GetText('success_delete') .'";
			var breeze_confirm_delete = "'. $text->GetText('confirm_delete') .'";
			var breeze_confirm_yes = "'. $text->GetText('confirm_yes') .'";
			var breeze_confirm_cancel = "'. $text->GetText('confirm_cancel') .'";
			var breeze_already_deleted = "'. $text->GetText('already_deleted') .'";
			var breeze_cannot_postStatus = "'. $text->GetText('cannot_postStatus') .'";
			var breeze_cannot_postComments = "'. $text->GetText('cannot_postComments') .'";
	// ]]></script>';


		/* Let's load jquery from CDN only if it hasn't been loaded yet */
		$context['html_headers'] .= '
		<link href="'. $settings['default_theme_url'] .'/css/breeze.css" rel="stylesheet" type="text/css" />
		<link href="'. $settings['default_theme_url'] .'/css/facebox.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery.min.js"%3E%3C/script%3E\'))</script>
		<script src="'. $settings['default_theme_url'] .'/js/jquery_notification.js" type="text/javascript"></script>
		<script src="'. $settings['default_theme_url'] .'/js/facebox.js" type="text/javascript"></script>
		<script src="'. $settings['default_theme_url'] .'/js/confirm.js" type="text/javascript"></script>
		<script src="'. $settings['default_theme_url'] .'/js/livequery.js" type="text/javascript"></script>
		<script src="'. $settings['default_theme_url'] .'/js/breeze.js" type="text/javascript"></script>';

		/* CSS part */
		$context['html_headers'] .= '
<style type="text/css">
.breeze_user_comment_avatar
{
	padding:5px;
}
</style>';

		if($admin)
		{
			$context['html_headers'] .= '
			<script src="'. $settings['default_theme_url'] .'/js/jquery.zrssfeed.min.js" type="text/javascript"></script>
			<script type="text/javascript">
var breeze_feed_error_message = "'. $text->GetText('feed_error_message') .'";

$(document).ready(function ()
{
	$(\'#breezelive\').rssfeed(\'http://missallsunday.com/index.php?action=.xml;type=rss;sa=recent;board=11;limit=5/\',
	{
		limit: 5,
		header: false,
		date: true,
		errormsg: breeze_feed_error_message
   });
});
 </script>
<style type="text/css">
#breezelive
{
	width:95%;
	margin:auto;
}
#breezelive ul
 {
	list-style-type: none;
	padding: 0px;
	margin: 0px;
 }
 #breezelive ul li
 {
	margin-left: 10px;
 }
</style>';
		}
	}

	/* Relative dates  http://www.zachstronaut.com/posts/2009/01/20/php-relative-date-time-string.html */
	public function TimeElapsed($ptime)
	{
		$text = BreezeSettings::getInstance();
		$etime = time() - $ptime;

		if ($etime < 1)
			return $text->GetText('time_just_now');

		$a = array(
			12 * 30 * 24 * 60 * 60	=> $text->GetText('time_year'),
			30 * 24 * 60 * 60		=> $text->GetText('time_month'),
			24 * 60 * 60			=> $text->GetText('time_day'),
			60 * 60					=> $text->GetText('time_hour'),
			60						=> $text->GetText('time_minute'),
			1						=> $text->GetText('time_second')
		);

		foreach ($a as $secs => $str)
		{
			$d = $etime / $secs;
			if ($d >= 1)
			{
				$r = round($d);
				return $r . ' ' . $str . ($r > 1 ? 's ' : ' '). $text->GetText('time_ago');
			}
		}
	}

	/* A function to cut-off a string */
	public function TruncateString($string, $limit, $break = ' ', $pad = '...')
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

	public function Remove($array, $val, $preserve_keys = true)
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

	public static function LoadUserInfo($id, $single = false)
	{
		global $memberContext;

		/* Must be an array */
		if (!$single && !is_array($id))
		{
			$id = array($id);

			/* Remove double entries */
			$id = array_unique($id);

			/* Setup the context for each user */
			$temp_return = array();
		}

		/* Load all the members up. */
		if (loadMemberData($id, false, 'profile'))
		{
			if (!$single)
				foreach ($id as $i)
				{
					loadMemberContext($i);
					$temp_return[$i] = $memberContext[$i];
				}

			elseif ($single)
				{
					loadMemberContext($id);
					$temp_return = $memberContext[$id];
				}

			return $temp_return;
		}

		else
			return false;
	}
}