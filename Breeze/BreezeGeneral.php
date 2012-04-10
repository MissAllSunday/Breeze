<?php

/**
 * BreezeGeneral
 *
 * The purpose of this file is to show a general wall where user can see tatus and updates from other users or buddies
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
	
	/* Wrapper functions */
	wrapper_breezeGeneral_singleStatus(){BreezeGeneral::singleStatus();}
	wrapper_breezeGeneral_singleComment(){BreezeGeneral::singleComment();}

class BreezeGeneral
{
	public static function Call()
	{
		Breeze::Load(array('Globals'));

		/* Handling the subactions */
		$sa = new BreezeGlobals('get');

		$subActions = array(
			'singleStatus' => 'wrapper_breezeGeneral_singleStatus',
			'singleComments' => 'wrapper_breezeGeneral_singleComment'
		);

		/* Does the subaction even exist? */
		if (in_array($sa->Raw('sa'), array_keys($subActions)))
			$subActions[$sa->Raw('sa')]();

		else
			self::Wall();
	}

	/* Get the latest entries of your buddies */
	public static function Wall()
	{
		global $txt, $scripturl, $context;

		loadLanguage('Breeze');
		loadtemplate('Breeze');
		Breeze::Load(array('Settings','Subs'));
		writeLog(true);

		/* Set all the page stuff */
		$context['page_title'] = 'demo';
		$context['sub_template'] = 'general_wall';
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=wall',
			'name' => 'demo'
		);

		/* Headers */
		BreezeSubs::Headers(true);
	}

	/* Show a single status with all it's comments */
	public static function singleStatus()
	{
		global $user_info;

		/* Load all we need */
		loadtemplate('Breeze');
		Breeze::Load(array(
			'Globals',
			'Query',
			'Settings'
		));

		/* Prepare all we need */
		$globals = new BreezeGlobals('get');
		$tools = BreezeSettings::getInstance();
		$query = new BreezeQuery();

		/* Set all the page stuff */
		$context['sub_template'] = 'singleStatus';
		$context['page_title'] = $text->GetText('singleStatus_pageTitle');
		$context['canonical_url'] = $scripturl . '?action=wall;sa=singlestatus;u=' . $context['member']['id'];

		/* get the status data */
		if ($globals->See('statusID') == false)
			$topicID = $user_info['id'];

		else
			$topicID = $globals->See('statusID');

		$status = $query->GetStatusByID($topicID);

		echo '<pre>';
		print_r($status);
		echo '</pre>';

	}
}