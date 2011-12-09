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
		Breeze::LoadMethod(array('Settings','Subs'));
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