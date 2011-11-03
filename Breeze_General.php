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

class Breeze_General extends Breeze
{
	function __construct()
	{
	
		/* We need the settings */
		parent::__construct();
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
