<?php

/**
 * Breeze_
 *
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica González.
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	global $smcFunc, $context;

	db_extend('packages');

	if (empty($context['uninstalling']))
	{
		/* Comments */
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

		/* Status */
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

		/* Logs */
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

		/* User Settings */
		$tables[] = array(
			'table_name' => '{db_prefix}breeze_visit_log',
			'columns' => array(
				array(
					'name' => 'user_id',
					'type' => 'int',
					'size' => 5,
					'null' => false,
				),
				array(
					'name' => 'enable_buddies',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'enable_visitors',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'enable_notification',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('user_id')
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);

		foreach ($tables as $table)
		$smcFunc['db_create_table']($table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);

	}

?>