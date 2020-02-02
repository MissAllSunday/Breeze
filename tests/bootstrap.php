<?php

declare(strict_types=1);

define('ROOT', __DIR__);
define('SMF', true);

// mock globals used by SMF
global $sourcedir, $scripturl, $modSettings;
global $boarddir, $boardurl, $context, $txt, $smcFunc;

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

$sourcedir = $scripturl = $boarddir = $boardurl = ROOT;

// Mock some SMF arrays.
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
    'time_year' => 'years',
    'time_month' => 'months',
    'time_day' => 'days',
    'time_hour' => 'hours',
    'time_minute' => 'minutes',
    'time_second' => 'seconds',
    'time_ago' => 'ago',
];

// Composer-Autoloader
require_once $_SERVER['DOCUMENT_ROOT'] . "./vendor/autoload.php";
