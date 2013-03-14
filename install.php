<?php

/**
 * install.php
 *
 * The purpose of this file is to create the tables Breeze needs.
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
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
 * Portions created by the Initial Developer are Copyright (c) 2012
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

	// Breeze needs php 5.3 or greater
	BreezeCheck();

	db_extend('packages');

	if (empty($context['uninstalling']))
	{
		// Profile views
		$smcFunc['db_add_column'](
			'{db_prefix}members',
			array(
				'name' => 'breeze_profile_views',
				'type' => 'text',
				'size' => '',
				'default' => '',
			),
			array(),
			'update',
			null
		);

		// Comments
		$tables[] = array(
			'table_name' => '{db_prefix}breeze_comments',
			'columns' => array(
				array(
					'name' => 'comments_id',
					'type' => 'int',
					'size' => 5,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'comments_status_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'comments_status_owner_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'comments_poster_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'comments_profile_owner_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'comments_time',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'comments_body',
					'type' => 'text',
					'size' => '',
					'default' => '',
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('comments_id')
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);

		// Status
		$tables[] = array(
			'table_name' => '{db_prefix}breeze_status',
			'columns' => array(
				array(
					'name' => 'status_id',
					'type' => 'int',
					'size' => 5,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'status_owner_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'status_poster_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'status_time',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'status_body',
					'type' => 'text',
					'size' => '',
					'default' => '',
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('status_id')
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);

		// Notifications
		$tables[] = array(
			'table_name' => '{db_prefix}breeze_notifications',
			'columns' => array(
				array(
					'name' => 'id',
					'type' => 'int',
					'size' => 5,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'user',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'user_to',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'type',
					'type' => 'text',
					'size' => '',
					'default' => '',
				),
				array(
					'name' => 'time',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'viewed',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'content',
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

		// Installing
		foreach ($tables as $table)
		$smcFunc['db_create_table']($table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);
	}

	function BreezeCheck()
	{
		if (version_compare(PHP_VERSION, '5.3.0', '<'))
			fatal_error('This mod needs PHP 5.3 or greater. You will not be able to install/use this mod, contact your host and ask for a php upgrade.');
	}
