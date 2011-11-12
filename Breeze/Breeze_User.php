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
	}

	public static function Wall()
	{
		global $txt, $scripturl, $context, $memID;

		loadLanguage('Breeze');
		loadtemplate('Breeze');
		LoadBreezeMethod(array(
			'Breeze_Settings',
			'Breeze_Subs',
			'Breeze_Globals'
		));
		Breeze_Subs::Headers();

		/* Set all the page stuff */
		$context['page_title'] = $txt['breeze_general_wall'];
		$context['sub_template'] = 'user_wall';



		/* Handling the subactions */
		$sa = Breeze_Globals::factory('get');

		$subActions = array(
			'post' => 'self::Post',
		/* More actions here... */
		);

		/* Does the subaction even exist? */
		if ($sa->validate('sa') && in_array($sa->see('sa'), $subActions))
			call_user_func($subActions[$sa->see('sa')]);

	}

	/* We deal with the status/comments here... */
	public static function Post()
	{
		/* We need all of this, really, we do. */
		LoadBreezeMethod(array(
			'Breeze_Settings',
			'Breeze_Subs',
			'Breeze_Post',
			'Breeze_Globals')
		);

		/* Acording to the type, we create a new instance to handle it properly */
		$p = Breeze_Globals::factory('post');

		if ($p->validate('type') && $p->see('type') == 'status')
			$value = Breeze_Post::factory('status', $p);

		elseif ($p->validate('type') && $p->see('type') == 'comment')
			$value = Breeze_Post::factory('comment', $p);

		/* Lets update the wall with the brand new status/comment */
		if ($value->ok)
		{
			echo json_encode($value->publish());

		/* Don't forget to write everything up to the logs */

		}

	}


}
?>