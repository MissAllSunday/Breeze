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

class Breeze_Ajax
{

	private function __construct()
	{
	}

	public static function factory()
	{
		LoadBreezeMethod('Breeze_Globals');
		loadtemplate('BreezeAjax');

		/* Handling the subactions */
		$sa = Breeze_Globals::factory('get');
		$subActions = array(
			'post' => 'self::Post',
		/* More actions here... */
		);

		/* Does the subaction even exist? */
		if ($sa->validate('sa') && in_array($sa->raw('sa'), array_keys($subActions)))
			call_user_func($subActions[$_GET['sa']]);
	}

	/* Deal with the status/comments... */
	public static function Post()
	{
		global $context;

		/* We need all of this, really, we do. */
		LoadBreezeMethod(array(
			'Breeze_Settings',
			'Breeze_Subs',
			'Breeze_Post',
			'Breeze_Globals'
		));

		/* Acording to the type, we create a new instance to handle it properly */


		/* This is a temp solution... testing porpuses only */
		$p = Breeze_Globals::factory('post');

		$context['breeze']['post']['status'] = '
			<div class="windowbg">
			<span class="topslice"><span></span></span>
				<div class="breeze_user_inner">
					<div class="breeze_user_statusbox">
						'.$p->see('content').'
					</div>
				</div>
			<span class="botslice"><span></span></span>
		</div>';

		$context['template_layers'] = array();
		$context['sub_template'] = 'post_status';
	}
}
?>