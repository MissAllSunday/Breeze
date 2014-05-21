<?php

/**
 * install.php
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
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
				'default' => null,
			),
			array(),
			'update',
			null
		);

		// Member options
		$tables[] = array(
			'table_name' => '{db_prefix}breeze_options',
			'columns' => array(
				array(
					'name' => 'member_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'variable',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'value',
					'type' => 'text',
					'size' => '',
					'default' => null,
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('member_id', 'variable')
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
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
					'name' => 'comments_profile_id',
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
					'default' => null,
				),
				array(
					'name' => 'likes',
					'type' => 'int',
					'size' => 5,
					'null' => false
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
					'default' => null,
				),
				array(
					'name' => 'likes',
					'type' => 'int',
					'size' => 5,
					'null' => false
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
					'name' => 'sender',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'receiver',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'type',
					'type' => 'varchar',
					'size' => 255,
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
					'default' => null,
				),
				array(
					'name' => 'type_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'second_type',
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

		// Installing
		foreach ($tables as $table)
			$smcFunc['db_create_table']($table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);
	}

	function BreezeCheck()
	{
		if (version_compare(PHP_VERSION, '5.3.0', '<'))
			fatal_error('This mod needs PHP 5.3 or greater. You will not be able to install/use this mod, contact your host and ask for a php upgrade.');
	}
