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

class Breeze_User extends Breeze
{
	/* Handle the actions and set the proper files to handle all */
	public function  __construct()
	{
		/* Is this thing even enable? */
		if (empty($this->breeze['global_settings']['enable']))
			return;

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

		call_user_func($subActions[$_REQUEST['sa']]);

		parent:: __construct();
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