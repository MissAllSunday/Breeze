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

class Breeze_Post
{
	private static $instances = array();
	private $post;
	private $publish = '';

	public static function factory($var, $data)
	{
		if (array_key_exists($var, self::$instances))
		{
			return self::$instances[$var];
		}
		if ($var == 'status')
			return self::$instances[$var] = new Breeze_Post('status', $data);
		elseif ($var == 'comment')
			return self::$instances[$var] = new Breeze_Post('comment', $data);

	}

	function __construct($data)
	{
		$this->value = $var;
		$this->ok = false;
		$this->data = $data;
	}

	function publish()
	{
		/* Write the html for the comment/status */
		if ($this->value == 'status')
			$publish = '';
			
			
		return $publish
	}
}

?>
