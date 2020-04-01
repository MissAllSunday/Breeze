<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Controller\AdminController;
use Breeze\Controller\User\CoverController;
use Breeze\Controller\User\Settings\AlertsController;
use Breeze\Controller\User\Settings\SettingsController;
use Breeze\Controller\User\WallController;
use Breeze\Service\AdminService;
use Breeze\Service\MoodService;
use Breeze\Service\RequestService;
use Breeze\Service\UserService;
use Breeze\Service\WallService;

return [
	'controller.admin' => [
		'class' => AdminController::class,
		'arguments'=> [
			RequestService::class,
			AdminService::class,
			MoodService::class,
		]
	],
	'controller.wall' => [
		'class' => WallController::class,
		'arguments'=> [
			RequestService::class,
			WallService::class,
			UserService::class
		]
	],
	'controller.alerts' => [
		'class' => AlertsController::class,
		'arguments'=> [
			RequestService::class,
			UserService::class,
		]
	],
	'controller.cover' => [
		'class' => CoverController::class,
		'arguments'=> [
			RequestService::class,
			UserService::class,
		]
	],
	'controller.settings' => [
		'class' => SettingsController::class,
		'arguments'=> [
			RequestService::class,
			UserService::class,
		]
	],
];
