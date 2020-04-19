<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Controller\AdminController;
use Breeze\Controller\API\StatusController;
use Breeze\Controller\User\Settings\AlertsController;
use Breeze\Controller\User\Settings\UserSettingsController;
use Breeze\Controller\User\WallController;
use Breeze\Service\Actions\AdminService;
use Breeze\Service\Actions\UserSettingsService;
use Breeze\Service\Actions\WallService;
use Breeze\Service\MoodService;
use Breeze\Service\StatusService;
use Breeze\Service\UserService;

return [
	'controller.admin' => [
		'class' => AdminController::class,
		'arguments'=> [
			AdminService::class,
			MoodService::class,
		]
	],
	'controller.wall' => [
		'class' => WallController::class,
		'arguments'=> [
			WallService::class,
			UserService::class
		]
	],
	'controller.status' => [
		'class' => StatusController::class,
		'arguments'=> [
			StatusService::class,
			UserService::class
		]
	],
	'controller.user.alerts' => [
		'class' => AlertsController::class,
		'arguments'=> [
			UserService::class,
		]
	],
	'controller.user.settings' => [
		'class' => UserSettingsController::class,
		'arguments'=> [
			UserSettingsService::class,
			UserService::class,
		]
	],
];
