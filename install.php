<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF')) {
	require_once(dirname(__FILE__) . '/SSI.php');
} elseif (!defined('SMF')) {
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');
}

global $smcFunc, $context;

BreezeCheck();

db_extend('packages');

if (empty($context['uninstalling'])) {
	// No charset/collation is passed in 'parameters' below on purpose: SMF's
	// smf_db_create_table() only honours $parameters['engine'] and emits no
	// charset clause of its own, so these tables inherit the forum database
	// default exactly like every core table does. Pinning a collation here
	// would risk a mismatch against the tables Breeze joins to (smf_members),
	// which forces a conversion and defeats index use on that side of the join.
	// tests/setup-test-database.php deliberately does the same.

	// Member options
	$tables[] = [
		'table_name' => '{db_prefix}breeze_options',
		'columns' => [
			[
				'name' => 'member_id',
				'type' => 'int',
				'size' => 4,
				'null' => false,
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
				'columns' => ['member_id','variable'],
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
				'name' => 'id',
				'type' => 'bigint',
				'size' => 8,
				'null' => false,
				'auto' => true,
			],
			[
				'name' => 'status_id',
				'type' => 'bigint',
				'size' => 8,
				'null' => false,
			],
			[
				'name' => 'user_id',
				'type' => 'int',
				'size' => 4,
				'null' => false,
			],
			[
				'name' => 'likes',
				'type' => 'int',
				'size' => 4,
				'null' => false,
			],
			[
				'name' => 'body',
				'type' => 'text',
				'size' => '',
				'default' => null,
			],
			[
				'name' => 'created_at',
				'type' => 'int',
				'size' => 11,
				'null' => false,
				'default' => 0,
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id'],
			],
			[
				'name' => 'idx_status_created_id',
				'type' => 'index',
				'columns' => ['status_id', 'created_at', 'id'],
			],
			[
				'name' => 'user_id',
				'type' => 'index',
				'columns' => ['user_id'],
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
				'name' => 'id',
				'type' => 'bigint',
				'size' => 8,
				'null' => false,
				'auto' => true,
			],
			[
				'name' => 'wall_id',
				'type' => 'int',
				'size' => 4,
				'null' => false,
			],
			[
				'name' => 'user_id',
				'type' => 'int',
				'size' => 4,
				'null' => false,
			],
			[
				'name' => 'likes',
				'type' => 'int',
				'size' => 4,
				'null' => false,
				'default' => 0,
			],
			[
				'name' => 'body',
				'type' => 'text',
				'size' => '',
				'default' => null,
			],
			[
				'name' => 'created_at',
				'type' => 'int',
				'size' => 11,
				'null' => false,
				'default' => 0,
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id'],
			],
			[
				'name' => 'idx_wall_created_id',
				'type' => 'index',
				'columns' => ['wall_id', 'created_at', 'id'],
			],
			[
				'name' => 'idx_user_created_id',
				'type' => 'index',
				'columns' => ['user_id', 'created_at', 'id'],
			],
		],
		'if_exists' => 'ignore',
		'error' => 'fatal',
		'parameters' => [],
	];

	// Installing
	foreach ($tables as $table) {
		$smcFunc['db_create_table'](
			$table['table_name'],
			$table['columns'],
			$table['indexes'],
			$table['parameters'],
			$table['if_exists'],
			$table['error']
		);
	}
}

function BreezeCheck(): void
{
	if (version_compare(\PHP_VERSION, '8.3.0', '<')) {
		fatal_error('This mod needs PHP 8.3 or greater.
		 You will not be able to install/use this mod,contact your host and ask for a PHP upgrade.');
	}
}
