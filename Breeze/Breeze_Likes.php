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

	/* Someone liked this, dunno why */
	public static function Breeze_Like($array, $entry = false)
	{
		global $sourcedir;

		require_once($sourcedir . '/breeze_DB.class.php');

		$data = array(
			'id_entry' => 'int',
			'id_comment' => 'int',
			'id_member' => 'string',
			'member_name' => 'string',
			'liked' => 'int'
		);
		$values = array(
			$entry ? $array['id_entry'] : 0,
			$entry ? 0 : $array['id_comment'],
			$array['id_member'],
			$array['member_name'],
			1
		);
		$indexes = array('id');

		$insert = new OharaDBClass('breeze_Likes');
		$insert->InsertData($data, $values, $indexes);

		/* Log this please */
		breeze_LogsWrite($params);
	}

	/* Me no longer like this */
	public static function breeze_Unlike($array, $entry = false)
	{
		global $sourcedir;

		require_once($sourcedir . '/breeze_DB.class.php');

		/* Is this an entry or a comment? */
		if ($entry)
			$id = 'id_entry = {int:id} AND id_member = {int:id_member}';

		else
			$id = 'id_comment = {int:id} AND id_member = {int:id_member}';

			$params = array(
				'set' => 'liked={int:liked}',
				'where' => $id,
			);

			$data = array(
				'liked' => 2,
				'id' => $array['id'],
				'id_member' => $array['id_member']
			);

			$arraydata = new OharaDBClass('breeze_Likes');
			$arraydata->Params($params, $data);
			$arraydata->UpdateData();
	}

