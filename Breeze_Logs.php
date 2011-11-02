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

	/* I see it all... */
class Breeze_Logs
{
	public static function Get($type, $limit = false)
	{
		/* Get the lastest logs from $type */

		$params = array(
			'rows' =>'id, id_entry, id_comment, type, date, user, name, action',
			'where' => 'type = {string:type}',
			'limit' => $limit ? '{int:limit}' : ''
		);

		$data = array(
			'type' => $type,
			'limit' => $limit ? $limit : 0
		);

		$query = new BreezeDB('breeze_Logs');
		$query->Params($params, $data);
		$query->GetData();

		if (!empty($query->data_result))
			return $query->data_result;
	}

	/* Cash bribes only */
	public static function Write($params)
	{
		global $user_info;

		foreach($params as $k => $v)
			$v = Breeze_Globals::Sanitize($v);

		/* Insert! */
		$data = array(
			'id_entry' => 'string',
			'id_comment' => 'int',
			'type' => 'string',
			'date' => 'int',
			'user' => 'int',
			'name' => 'string',
			'action' => 'string'
		);
		$values = array(
			empty($params['id_entry']) ? 0 : $params['id_entry'],
			empty($params['id_comment']) ? 0 : $params['id_comment'],
			empty($params['type']) ? 'null' : $params['type'],
			empty($params['date']) ? time() : $params['date'],
			empty($params['user']) ? $user_info['id'] : $params['user'],
			empty($params['name']) ? 'null' : $params['name'],
			empty($params['action'] ? 'null' : $params['action']
		);
		$indexes = array(
			'id'
		);
		$insert = new BreezeDB('breeze_logs');
		$insert->InsertData($data, $values, $indexes);
	}
}
?>
