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

	if (empty($context['uninstalling'])){

		$tables[] = array(
			'table_name' => '{db_prefix}breeze_comments',
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 5,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'id_entry_from',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'id_comment_from',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'is_entry',
					'type' => 'int',
					'size' => 1,
					'null' => false
				),
				array(
					'name' => 'likes',
					'type' => 'text',
					'size' => '',
				),
				array(
					'name' => 'date',
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
				array(
					'name' => 'id_user',
					'type' => 'int',
					'size' => 1,
					'null' => false
				),
				array(
					'name' => 'user_name',
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

		$tables[] = array(
			'table_name' => '{db_prefix}breeze_entries',
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 5,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'id_entry',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'likes',
					'type' => 'text',
					'size' => '',
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
			'table_name' => '{db_prefix}breeze_settings',
			'columns' => array(
				array(
					'name' => 'enable',
					'type' => 'int',
					'size' => 1,
					'null' => false
				),
				array(
					'name' => 'menu_position',
					'type' => 'varchar',
					'size' => 255,
					'null' => false
				),
				array(
					'name' => 'enable_general_wall',
					'type' => 'int',
					'size' => 1,
					'null' => false
				),
				array(
					'name' => 'rss_url',
					'type' => 'varchar',
					'size' => 255,
					'null' => false
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('enable')
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