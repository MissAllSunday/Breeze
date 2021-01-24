<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc, $context;

BreezeCheck();

db_extend('packages');

if (empty($context['uninstalling']))
{
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
				'name' => 'id',
				'type' => 'int',
				'size' => 5,
				'null' => false,
				'auto' => true
			],
			[
				'name' => 'statusId',
				'type' => 'int',
				'size' => 5,
				'null' => false
			],
			[
				'name' => 'userId',
				'type' => 'int',
				'size' => 5,
				'null' => false
			],
			[
				'name' => 'createdAt',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			],
			[
				'name' => 'body',
				'type' => 'text',
				'size' => '',
				'default' => null,
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id', 'statusId']
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
				'auto' => true
			],
			[
				'name' => 'wallId',
				'type' => 'int',
				'size' => 5,
				'null' => false
			],
			[
				'name' => 'userId',
				'type' => 'int',
				'size' => 5,
				'null' => false
			],
			[
				'name' => 'createdAt',
				'type' => 'varchar',
				'size' => 255,
				'default' => '',
			],
			[
				'name' => 'body',
				'type' => 'text',
				'size' => '',
				'default' => null,
			],
		],
		'indexes' => [
			[
				'type' => 'primary',
				'columns' => ['id', 'wallId']
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
				'auto' => true
			],
			[
				'name' => 'emoji',
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
				'name' => 'isActive',
				'type' => 'int',
				'size' => 1,
				'null' => false
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
				'columns' => ['id']
			],
		],
		'if_exists' => 'ignore',
		'error' => 'fatal',
		'parameters' => [],
	];

	// Installing
	foreach ($tables as $table)
		$smcFunc['db_create_table']($table['table_name'],
			$table['columns'],
			$table['indexes'],
			$table['parameters'],
			$table['if_exists'],
			$table['error']
		);

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
	$emojis = explode(',', trim('😀,😁, 😂, 🤣, 😃, 😄, 😅, 😆, 😉, 😊, 😋, 😎, 😍, 😘, 🥰, 😗, 😙, 😚, ☺️, 🙂, 🤗, 🤩, 🤔, 🤨, 😐, 😑, 😶, 🙄, 😏, 😣, 😥, 😮, 🤐, 😯, 😪, 😫, 😴, 😌, 😛, 😜, 😝, 🤤, 😒, 😓, 😔, 😕, 🙃, 🤑, 😲, ☹️, 🙁, 😖, 😞, 😟, 😤, 😢, 😭, 😦, 😧, 😨, 😩, 🤯, 😬, 😰, 😱, 🥵, 🥶, 😳, 🤪, 😵, 😡, 😠, 🤬, 😷, 🤒, 🤕, 🤢, 🤮, 🤧, 😇, 🤠, 🤡, 🥳, 🥴, 🥺, 🤥, 🤫, 🤭, 🧐, 🤓, 😈, 👿, 👹, 👺,💩'));


	foreach ($emojis as $emoji)
		$smcFunc['db_insert']('insert', '{db_prefix}breeze_moods', [
			'emoji' => 'string',
			'description' => 'string',
			'enable' => 'int',
		], [
			$smcFunc['htmlspecialchars']($emoji),
			'',
			1
		], ['id']);
	}

function BreezeCheck(): void
{
	if (version_compare(PHP_VERSION, '7.4.0', '<'))
		fatal_error('This mod needs PHP 7.4 or greater. You will not be able to install/use this mod, contact your host and ask for a php upgrade.');
}
