<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Miss All Sunday <missallsunday@simplemachines.org>
 * @copyright 2011 Miss All Sunday
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Breeze_General
{
	function __construct()
	{
	
		/* We need the settings */

	}
	/* Get the latest entries of your buddies */
	public static function Wall()
	{
		global $txt, $scripturl, $context;

		loadLanguage('Breeze');
		loadtemplate('Breeze');
		LoadBreezeMethod(array('Breeze_Settings','Breeze_Subs'));
		writeLog(true);

		/* Set all the page stuff */
		$context['page_title'] = $txt['breeze_general_wall'];
		$context['sub_template'] = 'general_wall';
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=wall',
			'name' => $txt['breeze_general_wall']
		);

		/* Headers */
		Breeze_Subs::Headers(true);
	}

	/* Get the latest entries of your buddies */
	public static function Get_Entries()
	{
	}

	/* Get the latest comments */
	public static function Get_Comments()
	{
	}
	
	public static function Get_Logs()
	{
	}
}
?>
