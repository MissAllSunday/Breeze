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
	
	/* A bunch of wrapper functions so static methods can be callable with a string by SMF */
	function Breeze_Wrapper_Wall(){Breeze_User::Wall();}
	function Breeze_Wrapper_Settings(){Breeze_User::Settings();}
	function Breeze_Wrapper_Permissions(){Breeze_User::Permissions();}

class Breeze_User
{
	/* Handle the actions and set the proper files to handle all */
	public function  __construct()
	{
	
	LoadBreezeMethod(array('Breeze_Subs', 'Breeze_Globals'));
	
		/* I comment this until I find a better way to get the settings */
/* 		if (empty($this->breeze['global_settings']['enable']))
			return; */

		/* Declare all subactions we are gonna need */
		$sub_actions = array(
		'wall' => 'Breeze_User::Wall',
		'entry' => 'Breeze_User::Entry',
		'comment' => 'Breeze_User::Comment',
		'single' => 'Breeze_User::Single',
		'like' => 'Breeze_Likes::Like',
		'dislike' => 'Breeze_Likes::Dislike',
		'delete_entry' => 'Breeze_User::Delete_Entry',
		'delete_comment' => 'Breeze_User::Delete_Comment',
	);

		Breeze_Subs::General_Headers();
		
		$sa = Breeze_Globals::factory('get');

		/* Does the page even exist? */
		if ($sa->validate('sa') && in_array($sa->see('sa'), $sub_actions))
			call_user_func($subActions[$sa->see('sa')]);
			
		/* By default we call the user's wall */
		else
			call_user_func($subActions['wall']);

		/* Done here, lets move on */
	}

	public static function Wall()
	{

	}

	public static function Settings()
	{

	}

	public static function Permissions()
	{

	}



}
?>