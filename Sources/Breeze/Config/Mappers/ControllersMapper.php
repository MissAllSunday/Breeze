<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Controller\AdminController;
use Breeze\Controller\API\CommentController;
use Breeze\Controller\API\LikesController;
use Breeze\Controller\API\MoodController;
use Breeze\Controller\API\StatusController;
use Breeze\Controller\User\Settings\AlertsController;
use Breeze\Controller\User\Settings\UserSettingsController;
use Breeze\Controller\User\WallController;
use Breeze\Repository\CommentRepository;
use Breeze\Repository\LikeRepository;
use Breeze\Repository\StatusRepository;
use Breeze\Service\Actions\AdminService;
use Breeze\Service\Actions\UserSettingsService;
use Breeze\Service\Actions\WallService;
use Breeze\Service\MoodService;
use Breeze\Service\UserService;
use Breeze\Util\Form\UserSettingsBuilder;
use Breeze\Util\Validate\ValidateGateway;

return [
	'controller.admin' => [
		'class' => AdminController::class,
		'arguments'=> [
			AdminService::class,
			MoodService::class,
		],
	],
	'controller.wall' => [
		'class' => WallController::class,
		'arguments'=> [
			WallService::class,
			UserService::class,
		],
	],
	'controller.status' => [
		'class' => StatusController::class,
		'arguments'=> [
			StatusRepository::class,
			ValidateGateway::class,
		],
	],
	'controller.comment' => [
		'class' => CommentController::class,
		'arguments'=> [
			CommentRepository::class,
			StatusRepository::class,
			ValidateGateway::class,
		],
	],
	'controller.user.alerts' => [
		'class' => AlertsController::class,
		'arguments'=> [
			UserService::class,
		],
	],
	'controller.user.settings' => [
		'class' => UserSettingsController::class,
		'arguments'=> [
			UserSettingsService::class,
			UserService::class,
			UserSettingsBuilder::class,
			ValidateGateway::class,
		],
	],
	'controller.mood' => [
		'class' => MoodController::class,
		'arguments'=> [
			UserService::class,
			UserSettingsService::class,
			MoodService::class,
			ValidateGateway::class,
		],
	],
	'controller.likes' => [
		'class' => LikesController::class,
		'arguments'=> [
			LikeRepository::class,
			ValidateGateway::class,
		],
	],
];
