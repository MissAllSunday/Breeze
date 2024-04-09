<?php

declare(strict_types=1);

define('ROOT', __DIR__);
define('SMF', true);

// mock globals used by SMF
global $sourcedir, $scripturl, $modSettings;
global $boarddir, $boardurl, $context, $txt, $smcFunc, $user_info;

// Function DB
$smcFunc['htmltrim'] = function ($value) {
	return trim($value);
};

$smcFunc['htmlspecialchars'] = function ($value) {
	return htmlspecialchars($value, \ENT_QUOTES);
};

// Mock functions
function timeformat($string = ''): string
{
	return $string;
}

function comma_format(string $number): string
{
	global $txt;
	static $thousands_separator = null, $decimal_separator = null, $decimal_count = null;

	$override_decimal_count = false;

	if ($decimal_separator === null) {
		if (empty($txt['number_format']) ||
			preg_match('~^1([^\d]*)?234([^\d]*)(0*?)$~', $txt['number_format'], $matches) != 1) {
			return $number;
		}

		$thousands_separator = $matches[1];
		$decimal_separator = $matches[2];
		$decimal_count = strlen($matches[3]);
	}

	return number_format((float) $number, 0, $decimal_separator, $thousands_separator);
}
function loadLanguage($template_name): void
{
}
function log_error($string): void
{
}
function add_integration_function(): void
{
}
function remove_integration_function(): void
{
}
function smf_json_decode($s, $array = true)
{
	return json_decode($s, $array);
}

function parse_bbc(string $content): string
{
	return $content;
}

function loadMemberData(array $userIds): array
{
	return in_array(2, $userIds) ? [] : $userIds;
}

function loadMemberContext(int $userId, bool $dummy): array
{
	switch ($userId) {
		case 666:
			$dataToReturn = [
				'link' => '<a href="#">Astaroth</a>',
				'name' => 'Astaroth',
				'avatar' => ['href' => 'avatar_url/astaroth.png'],
			];

			break;
		case 1:
			$dummy = true;
			$dataToReturn = [
				'link' => 'Guest',
				'name' => 'Guest',
				'avatar' => ['href' => 'avatar_url/default.png'],
			];

			break;
		default:
			$dataToReturn = [];
	}

	if ($dummy) {
		$dummy = false;
	}

	return $dataToReturn;
}

function allowedTo($permissionName)
{
	$dummyPermissions = [
		'breeze_nope' => false,
		'breeze_yep' => true,
		'postComments' => false,
		'deleteComments' => false,
		'deleteOwnComments' => false,
		'deleteOwnStatus' => false,
		'deleteStatus' => false,
		'postStatus' => false,
		'admin_forum' => false,
		'likes_like' => false,
		'breeze_deleteProfileStatus' => true,
	];

	return $dummyPermissions[$permissionName] ?? false;
}

function cache_get_data($key, $timeToLive = 360): ?array
{
	return match ($key) {
		'Breeze_getByProfile1' => [],
		'Breeze_getByProfile2' => [
			1 => [
				'id' => 1,
				'wallId' => 1,
				'userId' => 1,
				'createdAt' => 581299200,
				'body' => 'status body',
				'likes' => 0,
				'comments' => [
					1 => [
						'id' => 1,
						'statusId' => 1,
						'userId' => 1,
						'createdAt' => 581299200,
						'body' => 'comment body',
						'likes' => 0,
					],
				],
				'likesInfo' => [
					'contentId' => 1,
					'count' => 0,
					'alreadyLiked' => false,
					'type' => 'type',
					'canLike' => false,
					'additionalInfo' => '',
				],
				'userData' => [
					'link' => 'Guest',
					'name' => 'Guest',
					'avatar' => ['href' => 'avatar_url/default.png'],
				],
			],],
//		'Breeze_StatusRepository_getByProfile1' => [
//			'usersIds' => [1],
//			'data' => [
//				1 => [
//					'id' => 666,
//					'wallId' => 666,
//					'userId' => 1,
//					'createdAt' => 581299200,
//					'body' => 'some body',
//					'likes' => [],
//				],],
//		],
//		'Breeze_StatusRepository_getByProfile2' => [
//			'usersIds' => [],
//			'data' => [],
//		],
//		'Breeze_CommentRepository_getByProfile1' => [
//			'usersIds' => [1,2,3],
//			'data' => [
//				1 => [
//					1 => [
//						'id' => 1,
//						'statusId' => 1,
//						'userId' => 1,
//						'createdAt' => 581299200,
//						'body' => 'comment body',
//						'likes' => 0,
//						'likesInfo' => [],
//						'userData' => [
//							'link' => 'Guest',
//							'name' => 'Guest',
//							'avatar' => ['href' => 'avatar_url/default.png'],
//						],
//					],
//				], ],
//		],
		'user_settings_666' => [
			'generalWall' => 1,
		],
		default => null,
	};
}

function cache_put_data($key, $data, $timeToLive)
{
	return null;
}

$sourcedir = $boarddir = $boardurl = ROOT;
$scripturl = 'localhost';

// Mock some SMF arrays.
$user_info = [
	'id' => 666,
	'is_guest' => false,
];

$context = [
	'session_var' => 'foo',
	'session_id' => 'baz',
	'cust_profile_fields_placement' => [
		'standard',
		'icons',
		'above_signature',
		'below_signature',
		'below_avatar',
		'above_member',
		'bottom_poster',
		'before_member',
		'after_member',
	],
];

$modSettings = [
	'CompressedOutput' => false,
	'Breeze_someSetting' => 666,
	'Breeze_master' => true,
	'Breeze_time_machine' => false,
	'avatar_url' => 'avatar_url',
	'enable_likes' => false,
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
	'guest_title' => 'Guest',
	'number_format' => '1,234.00',
	'likes_1' => '<a href="%1$s">%2$s person</a> likes this.',
	'likes_n' => '<a href="%1$s">%2$s people</a> like this.',
	'you_likes_0' => 'You like this.',
	'you_likes_1' => 'You and <a href="%1$s">%2$s other person</a> like this.',
	'you_likes_n' => 'You and <a href="%1$s">%2$s other people</a> like this.',
];

$_REQUEST = [
	'xss' => '<script>alert("XSS")</script>',
	'div-image' => '<DIV »
STYLE="background-image: »
url(javascript:alert(\'XSS\')) »
">',
	'foo' => 'baz',
	'url-encoding' => '<A »
HREF="http://%77%77%77%2E%67 »
%6F%6F%67%6C%65%2E%63%6F%6D" »
>XSS</A>',
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
