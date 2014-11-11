<?php

/**
 * install.php
 *
 * @package Breeze mod
 * @version 1.1
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

		// Breeze own alert tables.
		$tables[] = array(
			'table_name' => '{db_prefix}breeze_logs',
			'columns' => array(
				array(
					'name' => 'id_log',
					'type' => 'int',
					'size' => 10,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'member',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'content_type',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'content_id',
					'type' => 'int',
					'size' => 10,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'time',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'extra',
					'type' => 'text',
					'size' => '',
					'default' => null,
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('id_log')
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);

		// My mood
		$tables[] = array(
			'table_name' => '{db_prefix}breeze_moods',
			'columns' => array(
				array(
					'name' => 'moods_id',
					'type' => 'int',
					'size' => 5,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'name',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'file',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'ext',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'description',
					'type' => 'text',
					'size' => '',
					'default' => null,
				),
				array(
					'name' => 'enable',
					'type' => 'int',
					'size' => 1,
					'null' => false
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('moods_id')
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);

		// Installing
		foreach ($tables as $table)
			$smcFunc['db_create_table']($table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);

		// Add the extra columns
		$smcFunc['db_add_column'](
			'{db_prefix}breeze_status',
			array(
				'name' => 'likes',
				'type' => 'int',
				'size' => 5,
				'null' => false
			),
			array(),
			'update',
			null
		);
		$smcFunc['db_add_column'](
			'{db_prefix}breeze_comments',
			array(
				'name' => 'likes',
				'type' => 'int',
				'size' => 5,
				'null' => false
			),
			array(),
			'update',
			null
		);

		// Lastly, insert the default moods and oh boy there are a lot!!!
		$moods = array('angel', 'angry', 'bear', 'beer', 'blush', 'brokenheart', 'cash', 'clapping', 'cool', 'crying', 'doh', 'drunk', 'dull', 'envy', 'evil', 'evilgrin', 'giggle', 'happy', 'headbang', 'hi', 'inlove', 'itwasntme', 'kiss', 'lipssealed', 'makeup', 'middlefinger', 'mmm', 'mooning', 'muscle', 'nerd', 'party', 'pizza', 'puke', 'rock', 'sad', 'sleepy', 'smile', 'smoke', 'speechless', 'sunny', 'surprised', 'sweating', 'talking', 'thinking', 'tongueout', 'wait', 'wink', 'wondering', 'worried', 'yawn', );

		foreach ($moods as $m)
			$smcFunc['db_insert']('replace', '{db_prefix}breeze_moods', array(
				'name' => 'string',
				'file' => 'string',
				'ext' => 'string',
				'description' => 'string',
				'enable' => 'int',
			), array(
				$m,
				$m,
				'gif',
				$m,
				1
			), array('moods_id', ));
	}

	function BreezeCheck()
	{
		if (version_compare(PHP_VERSION, '5.3.0', '<'))
			fatal_error('This mod needs PHP 5.3 or greater. You will not be able to install/use this mod, contact your host and ask for a php upgrade.');
	}
