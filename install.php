<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	global $smcFunc, $context;

	db_extend('packages');

	if (empty($context['uninstalling']))
	{
		$tables[] = array(
			'table_name' => '{db_prefix}breeze_comment',
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 5,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'status_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'status_owner_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'poster_comment_id',
					'type' => 'int',
					'size' => 1,
					'null' => false
				),
				array(
					'name' => 'profile_owner_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'time',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'body',
					'type' => 'text',
					'size' => '',
					'default' => '',
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);

		$tables[] = array(
			'table_name' => '{db_prefix}breeze_status',
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 5,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'owner_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'poster_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'time',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'body',
					'type' => 'text',
					'size' => '',
					'default' => '',
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);

		$tables[] = array(
			'table_name' => '{db_prefix}breeze_visit_log',
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 5,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'profile',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'user',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'time',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id')
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);

		foreach ($tables as $table)
		$smcFunc['db_create_table']($table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);

		$rows = array();
		$rows[] = array(
			'method' => 'ignore',
			'table_name' => '{db_prefix}breeze_settings',
			'columns' => array(
				'rss_url' => 'string'
			),
			'data' => array(
				'http://missallsunday.com/.xml/type.rss/'  // Temp url
			),
			'keys' => array(
				'enable'
			)
		);

		foreach ($rows as $row)
			$smcFunc['db_insert']($row['method'], $row['table_name'], $row['columns'], $row['data'], $row['keys']);

	}

?>