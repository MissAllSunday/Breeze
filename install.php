<?php

declare(strict_types=1);

/**
 * install.php
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2019, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
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
		    [
		        'name' => 'breeze_profile_views',
		        'type' => 'int',
		        'size' => 5,
		        'null' => false,
		        'default' => 0
		    ]
		);

		// Member options
		$tables[] = [
		    'table_name' => '{db_prefix}breeze_options',
		    'columns' => [
		        [
		            'name' => 'member_id',
		            'type' => 'int',
		            'size' => 5,
		            'null' => false
		        ],
		        [
		            'name' => 'variable',
		            'type' => 'varchar',
		            'size' => 255,
		            'default' => '',
		        ],
		        [
		            'name' => 'value',
		            'type' => 'text',
		            'size' => '',
		            'default' => null,
		        ],
		    ],
		    'indexes' => [
		        [
		            'type' => 'primary',
		            'columns' => ['member_id', 'variable']
		        ],
		    ],
		    'if_exists' => 'ignore',
		    'error' => 'fatal',
		    'parameters' => [],
		];

		// Comments
		$tables[] = [
		    'table_name' => '{db_prefix}breeze_comments',
		    'columns' => [
		        [
		            'name' => 'comments_id',
		            'type' => 'int',
		            'size' => 5,
		            'null' => false,
		            'auto' => true
		        ],
		        [
		            'name' => 'comments_status_id',
		            'type' => 'int',
		            'size' => 5,
		            'null' => false
		        ],
		        [
		            'name' => 'comments_status_owner_id',
		            'type' => 'int',
		            'size' => 5,
		            'null' => false
		        ],
		        [
		            'name' => 'comments_poster_id',
		            'type' => 'int',
		            'size' => 5,
		            'null' => false
		        ],
		        [
		            'name' => 'comments_profile_id',
		            'type' => 'int',
		            'size' => 5,
		            'null' => false
		        ],
		        [
		            'name' => 'comments_time',
		            'type' => 'varchar',
		            'size' => 255,
		            'default' => '',
		        ],
		        [
		            'name' => 'comments_body',
		            'type' => 'text',
		            'size' => '',
		            'default' => null,
		        ],
		    ],
		    'indexes' => [
		        [
		            'type' => 'primary',
		            'columns' => ['comments_id']
		        ],
		    ],
		    'if_exists' => 'ignore',
		    'error' => 'fatal',
		    'parameters' => [],
		];

		// Status
		$tables[] = [
		    'table_name' => '{db_prefix}breeze_status',
		    'columns' => [
		        [
		            'name' => 'status_id',
		            'type' => 'int',
		            'size' => 5,
		            'null' => false,
		            'auto' => true
		        ],
		        [
		            'name' => 'status_owner_id',
		            'type' => 'int',
		            'size' => 5,
		            'null' => false
		        ],
		        [
		            'name' => 'status_poster_id',
		            'type' => 'int',
		            'size' => 5,
		            'null' => false
		        ],
		        [
		            'name' => 'status_time',
		            'type' => 'varchar',
		            'size' => 255,
		            'default' => '',
		        ],
		        [
		            'name' => 'status_body',
		            'type' => 'text',
		            'size' => '',
		            'default' => null,
		        ],
		    ],
		    'indexes' => [
		        [
		            'type' => 'primary',
		            'columns' => ['status_id']
		        ],
		    ],
		    'if_exists' => 'ignore',
		    'error' => 'fatal',
		    'parameters' => [],
		];

		// Breeze own alert tables.
		$tables[] = [
		    'table_name' => '{db_prefix}breeze_logs',
		    'columns' => [
		        [
		            'name' => 'id_log',
		            'type' => 'int',
		            'size' => 10,
		            'null' => false,
		            'auto' => true
		        ],
		        [
		            'name' => 'member',
		            'type' => 'int',
		            'size' => 5,
		            'null' => false
		        ],
		        [
		            'name' => 'content_type',
		            'type' => 'varchar',
		            'size' => 255,
		            'default' => '',
		        ],
		        [
		            'name' => 'content_id',
		            'type' => 'int',
		            'size' => 10,
		            'null' => false,
		        ],
		        [
		            'name' => 'time',
		            'type' => 'int',
		            'size' => 5,
		            'null' => false
		        ],
		        [
		            'name' => 'extra',
		            'type' => 'text',
		            'size' => '',
		            'default' => null,
		        ],
		    ],
		    'indexes' => [
		        [
		            'type' => 'primary',
		            'columns' => ['id_log']
		        ],
		    ],
		    'if_exists' => 'ignore',
		    'error' => 'fatal',
		    'parameters' => [],
		];

		// My mood
		$tables[] = [
		    'table_name' => '{db_prefix}breeze_moods',
		    'columns' => [
		        [
		            'name' => 'moods_id',
		            'type' => 'int',
		            'size' => 5,
		            'null' => false,
		            'auto' => true
		        ],
		        [
		            'name' => 'name',
		            'type' => 'varchar',
		            'size' => 255,
		            'default' => '',
		        ],
		        [
		            'name' => 'file',
		            'type' => 'varchar',
		            'size' => 255,
		            'default' => '',
		        ],
		        [
		            'name' => 'ext',
		            'type' => 'varchar',
		            'size' => 255,
		            'default' => '',
		        ],
		        [
		            'name' => 'description',
		            'type' => 'text',
		            'size' => '',
		            'default' => null,
		        ],
		        [
		            'name' => 'enable',
		            'type' => 'int',
		            'size' => 1,
		            'null' => false
		        ],
		    ],
		    'indexes' => [
		        [
		            'type' => 'primary',
		            'columns' => ['moods_id']
		        ],
		    ],
		    'if_exists' => 'ignore',
		    'error' => 'fatal',
		    'parameters' => [],
		];

		// Installing
		foreach ($tables as $table)
			$smcFunc['db_create_table']($table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);

		// Add the extra columns
		$smcFunc['db_add_column'](
		    '{db_prefix}breeze_status',
		    [
		        'name' => 'likes',
		        'type' => 'int',
		        'size' => 5,
		        'null' => false
		    ],
		    [],
		    'update',
		    null
		);
		$smcFunc['db_add_column'](
		    '{db_prefix}breeze_comments',
		    [
		        'name' => 'likes',
		        'type' => 'int',
		        'size' => 5,
		        'null' => false
		    ],
		    [],
		    'update',
		    null
		);

		// Lastly, insert the default moods and oh boy there are a lot!!!
		$moods = ['angel', 'angry', 'bear', 'beer', 'blush', 'brokenheart', 'cash', 'clapping', 'cool', 'crying', 'doh', 'drunk', 'dull', 'envy', 'evil', 'evilgrin', 'giggle', 'happy', 'headbang', 'hi', 'inlove', 'itwasntme', 'kiss', 'lipssealed', 'makeup', 'middlefinger', 'mmm', 'mooning', 'muscle', 'nerd', 'party', 'pizza', 'puke', 'rock', 'sad', 'sleepy', 'smile', 'smoke', 'speechless', 'sunny', 'surprised', 'sweating', 'talking', 'thinking', 'tongueout', 'wait', 'wink', 'wondering', 'worried', 'yawn', ];

		foreach ($moods as $m)
			$smcFunc['db_insert']('replace', '{db_prefix}breeze_moods', [
			    'name' => 'string',
			    'file' => 'string',
			    'ext' => 'string',
			    'description' => 'string',
			    'enable' => 'int',
			], [
			    $m,
			    $m,
			    'gif',
			    $m,
			    1
			], ['moods_id', ]);
	}

	function BreezeCheck()
	{
		if (version_compare(PHP_VERSION, '5.3.0', '<'))
			fatal_error('This mod needs PHP 5.3 or greater. You will not be able to install/use this mod, contact your host and ask for a php upgrade.');
	}
