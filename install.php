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
	// Member options
	$tables[] = [
		'table_name' => '{db_prefix}breeze_options',
		'columns' => [
			[
				'name' => 'member_id',
				'type' => 'int',
				'size' => 5,
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
				'type' => 'int',
				'size' => 5,
				'null' => false,
				'auto' => true,
			],
			[
				'name' => 'statusId',
				'type' => 'int',
				'size' => 5,
				'null' => false,
			],
			[
				'name' => 'userId',
				'type' => 'int',
				'size' => 5,
				'null' => false,
			],
			[
				'name' => 'likes',
				'type' => 'int',
				'size' => 5,
				'null' => false,
			],
			[
				'name' => 'body',
				'type' => 'text',
				'size' => '',
				'default' => null,
			],
			[
				'name' => 'createdAt',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id','statusId'],
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
				'type' => 'int',
				'size' => 5,
				'null' => false,
				'auto' => true,
			],
			[
				'name' => 'wallId',
				'type' => 'int',
				'size' => 5,
				'null' => false,
			],
			[
				'name' => 'userId',
				'type' => 'int',
				'size' => 5,
				'null' => false,
			],
			[
				'name' => 'likes',
				'type' => 'int',
				'size' => 5,
				'null' => false,
			],
			[
				'name' => 'body',
				'type' => 'text',
				'size' => '',
				'default' => null,
			],
			[
				'name' => 'createdAt',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id','wallId'],
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
				'name' => 'id',
				'type' => 'int',
				'size' => 5,
				'null' => false,
				'auto' => true,
			],
			[
				'name' => 'emoji',
				'type' => 'int',
				'size' => 6,
				'default' => 0,
				'null' => false,
			],
			[
				'name' => 'description',
				'type' => 'text',
				'size' => '',
				'default' => '',
			],
			[
				'name' => 'isActive',
				'type' => 'int',
				'size' => 1,
				'null' => false,
			],
			[
				'name' => 'createdAt',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			],
			[
				'name' => 'updatedAt',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id'],
			],
		],
		'if_exists' => 'ignore',
		'error' => 'fatal',
		'parameters' => [],
	];

	// Activity
	$tables[] = [
		'table_name' => '{db_prefix}breeze_activities',
		'columns' => [
			[
				'name' => 'id',
				'type' => 'int',
				'size' => 5,
				'null' => false,
				'auto' => true,
			],
			[
				'name' => 'name',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			],
			[
				'name' => 'userId',
				'type' => 'int',
				'size' => 5,
				'null' => false,
			],
			[
				'name' => 'contentId',
				'type' => 'int',
				'size' => 5,
				'null' => true,
			],
			[
				'name' => 'extra',
				'type' => 'text',
				'size' => '',
				'default' => null,
			],
			[
				'name' => 'createdAt',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id', 'userId', 'contentId'],
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

	// Lastly, insert the default moods and oh boy there are a lot!!!
	$emojis = [
		128512,128513,128514,129315,128515,128516,128517128518,128521,128522,128523,128526,128525,128536,128535,128537,
		128538,9786,128578,129303,129300,128528,128529,128566,128580,128527,128547,128549,128558,129296,128559,128554,
		128555,128564,128524,129299,128539,128540,128541,129316,128530,128531,128532,128533,128579,129297,128562,128577,
		128534,128542,128543,128548,128546,128557,128550,128551,128552,128553,128556,128560,128561,128563,128565,128545,
		128544,128519,129312,129313,129317,128567,129298,129301,129314,129319,128520,128127,128128,129302,128169,128125,
	];


	foreach ($emojis as $emojiDecimal) {
		$smcFunc['db_insert']('insert', '{db_prefix}breeze_moods', [
			'emoji' => 'int',
			'description' => 'string',
			'isActive' => 'int',
			'createdAt' => 'string',
			'updatedAt' => 'string',
		], [
			$emojiDecimal,
			'',
			1,
			time(),
			'',
		], ['id']);
	}
}

function BreezeCheck(): void
{
	if (version_compare(\PHP_VERSION, '8.1.0', '<')) {
		fatal_error('This mod needs PHP 8.1 or greater.
		 You will not be able to install/use this mod,contact your host and ask for a PHP upgrade.');
	}
}
