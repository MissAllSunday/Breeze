<?php

declare(strict_types=1);

define('ROOT', __DIR__);
define('SMF', true);

// mock globals used by SMF
global $sourcedir, $scripturl, $modSettings;
global $boarddir, $boardurl, $context, $txt, $smcFunc, $user_info;

// Function DB
$smcFunc['htmltrim'] = function($value)
{
	return trim($value);
};

$smcFunc['htmlspecialchars'] = function($value)
{
	return htmlspecialchars($value, ENT_QUOTES);
};

// Mock functions
function loadLanguage($template_name): void{}
function log_error($string): void{}
function add_integration_function(): void{}
function remove_integration_function(): void{}
function smf_json_decode($s, $array = true)
{
	return json_decode($s, $array);
}

function allowedTo($permissionName)
{
	$dummyPermissions = [
		'nope' => false,
		'yep' => true,
		'postComments' => false,
		'deleteComments' => false,
		'deleteOwnComments' => false,
	];

	return $dummyPermissions[$permissionName];
}

function cache_get_data($key, $timeToLive = 360): ?array
{
	switch($key)
	{
		case 'Breeze_StatusRepository_getByProfile1':
		case 'Breeze_CommentRepository_getByProfile1':
			$dataToReturn = [
				'some data'
			];
			break;
		case 'user_settings_666':
			$dataToReturn = [
				'generalWall' => 1
			];
			break;
		default:
			$dataToReturn = null;
	}

	return $dataToReturn;
}

function cache_put_data($key, $data, $timeToLive)
{
	return null;
}

$sourcedir = $scripturl = $boarddir = $boardurl = ROOT;

// Mock some SMF arrays.
$user_info = [
	'id' => 666
];

$context = [
	'session_var' => 'foo',
	'session_id' => 'baz',
];
$modSettings = [
	'CompressedOutput' => false,
	'Breeze_someSetting' => 666,
	'Breeze_master' => true,
	'Breeze_time_machine' => false
];

$txt = [
	'time_year' => 'year',
	'time_month' => 'month',
	'time_day' => 'day',
	'time_hour' => 'hour',
	'time_minute' => 'minute',
	'time_second' => 'second',
	'time_ago' => 'ago',
	'time_just_now' => 'just now',
	'Breeze_lol' => 'lol',
];

$_REQUEST = [
	'xss' => '<script>alert("XSS")</script>',
	'foo' => 'baz',
];

$_SESSION['Breeze'] = [];
$_SESSION['Breeze']['notice'] = [
	'message' => 'Kaizoku ou ni ore wa naru',
	'type' => 'info',
];

$_SESSION['Breeze']['flood_1'] = [
	'time' => time() + 10,
	'msgCount' => 0,
];

$_SESSION['Breeze']['flood_2'] = [
	'time' => time() + 10,
	'msgCount' => 666,
];

$_SESSION['Breeze']['flood_3'] = [
	'time' => time() - 10,
	'msgCount' => 666,
];

$_SESSION['Breeze']['flood_4'] = [
	'time' => time() - 10,
	'msgCount' => 3,
];

// Composer-Autoloader
require_once $_SERVER['DOCUMENT_ROOT'] . "./breezeVendor/autoload.php";
