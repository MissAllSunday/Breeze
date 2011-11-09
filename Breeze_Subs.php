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
			'php' => '',
			'json' => ''
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
		global $context, $settings;

		/* Let's load jquery from google or microsoft CDN only if it hasn't been loaded yet */
			$context['html_headers'] .= '
			<script type="text/javascript">
	function loadScript(src, callback) {
		var head=document.getElementsByTagName(\'head\')[0];
		var script= document.createElement(\'script\');
		script.type= \'text/javascript\';
		script.onreadystatechange = function () {
			if (this.readyState == \'complete\' || this.readyState == \'loaded\') {
				callback();
			}
		}
		script.onload = callback;
		script.src = src;
		head.appendChild(script);
	}

	function isjQueryLoaded() {
		return (typeof jQuery !== \'undefined\');
	}

	function tryLoadChain() {
		var chain = arguments;
		if (!isjQueryLoaded()) {
			if (chain.length) {
				loadScript(
					chain[0],
					function() {
						tryLoadChain.apply(this, Array.prototype.slice.call(chain, 1));
					}
				);
			}
		}
	}
tryLoadChain(
		\'https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js\',
		\'http://ajax.microsoft.com/ajax/jQuery/jquery-1.6.4.min.js\');
	</script>
			<script src="'. $settings['theme_url']. '/js/breeze.js" type="text/javascript"></script>
			<link href="'. $settings['theme_url']. '/css/breeze.css" rel="stylesheet" type="text/css" />';

		if($admin)
		{
			$context['html_headers'] .= '
			<script src="'. $settings['theme_url']. '/js/jquery.zrssfeed.min.js" type="text/javascript"></script>';
		}
	}

	/* Relative dates  <http://www.zachstronaut.com/posts/2009/01/20/php-relative-date-time-string.html> */
	public static function Time_Elapsed($ptime)
	{
		global $txt;

		loadLanguage('Breeze');

		$etime = time() - $ptime;

		if ($etime < 1)
			return '0 '.$txt['breeze_time_second'];

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
				return $r . ' ' . $str . ($r > 1 ? 's' : '');
			}
		}
	}
}
?>