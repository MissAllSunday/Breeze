<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Breeze_Subs {

	function __construct()
	{
	}

	/* I can has fun? */
	public static function Check_Versions()
	{
		global $txt;

		$checkFor = array(
			'php',
			'json'
		);

		$versions = array(
			'php' => $txt['breeze_admin_settings_php_ok'],
			'json' => sprintf($txt['breeze_admin_settings_server_needs_ok'], $txt['breeze_admin_settings_json'])
		);

		loadLanguage('Breeze');

		if (in_array('json', $checkFor) && !function_exists('json_decode') && !function_exists('json_encode'))
			$versions['json'] = sprintf($txt['breeze_admin_settings_server_needs'], $txt['breeze_admin_settings_json']);

		if (@version_compare(PHP_VERSION, '5.3') == -1 && in_array('php', $checkFor))
			$versions['php'] = sprintf($txt['breeze_admin_settings_php'], PHP_VERSION);

		return $versions;
	}

	/* Headers */
	public static function Headers($admin = false)
	{
		global $context, $settings, $txt;

		loadLanguage('Breeze');

		/* Define some variables for the ajax stuff */
		$context['html_headers'] .= '
		<script type="text/javascript"><!-- // --><![CDATA[
			var breeze_error_message = "'.$txt['breeze_error_message'].'";
			var breeze_success_message = "'.$txt['breeze_success_message'].'";
			var breeze_empty_message = "'.$txt['breeze_empty_message'].'";
			var breeze_error_delete = "'.$txt['breeze_error_message'].'";
			var breeze_success_delete = "'.$txt['breeze_success_delete'].'";
			var breeze_confirm_delete = "'.$txt['breeze_confirm_delete'].'";
			var breeze_confirm_yes = "'.$txt['breeze_confirm_yes'].'";
			var breeze_confirm_cancel = "'.$txt['breeze_confirm_cancel'].'";
			var breeze_already_deleted = "'.$txt['breeze_already_deleted'].'";
	// ]]></script>';


		/* Let's load jquery from google or microsoft CDN only if it hasn't been loaded yet */
		$context['html_headers'] .= '
		<link href="'. $settings['theme_url']. '/css/breeze.css" rel="stylesheet" type="text/css" />
		<link href="'. $settings['theme_url']. '/css/facebox.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript">
			if (typeof jQuery == \'undefined\')
			{
				document.write("<script src=\'https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js\'><\/script>");
			}
		</script>
		<script src="'. $settings['theme_url']. '/js/jquery_notification.js" type="text/javascript"></script>
		<script src="'. $settings['theme_url']. '/js/facebox.js" type="text/javascript"></script>
		<script src="'. $settings['theme_url']. '/js/confirm.js" type="text/javascript"></script>
		<script src="'. $settings['theme_url']. '/js/livequery.js" type="text/javascript"></script>
		<script src="'. $settings['theme_url']. '/js/breeze.js" type="text/javascript"></script>';

		if($admin)
		{
			$context['html_headers'] .= '
			<script src="'. $settings['theme_url']. '/js/jquery.zrssfeed.min.js" type="text/javascript"></script>';
		}
	}

	/* Relative dates  http://www.zachstronaut.com/posts/2009/01/20/php-relative-date-time-string.html */
	public static function Time_Elapsed($ptime)
	{
		global $txt;

		loadLanguage('Breeze');

		$etime = time() - $ptime;

		if ($etime < 1)
			return $txt['breeze_time_just_now'];

		$a = array(
			12 * 30 * 24 * 60 * 60	=> $txt['breeze_time_year'],
			30 * 24 * 60 * 60		=> $txt['breeze_time_month'],
			24 * 60 * 60			=> $txt['breeze_time_day'],
			60 * 60					=> $txt['breeze_time_hour'],
			60						=> $txt['breeze_time_minute'],
			1						=> $txt['breeze_time_second']
		);

		foreach ($a as $secs => $str)
		{
			$d = $etime / $secs;
			if ($d >= 1)
			{
				$r = round($d);
				return $r . ' ' . $str . ($r > 1 ? 's ' : '').$txt['breeze_time_ago'];
			}
		}
	}

	/* A function to cut-off a string */
	public static function Truncate_String($string, $limit, $break = ' ', $pad = '...')
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
}