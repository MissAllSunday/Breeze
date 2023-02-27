<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Controller\AdminController;
use Breeze\Controller\API\CommentController;
use Breeze\Controller\API\LikesController;
use Breeze\Controller\API\StatusController;
use Breeze\Controller\User\Settings\AlertsController;
use Breeze\Controller\User\Settings\UserSettingsController;
use Breeze\Controller\User\WallController;
use Breeze\Repository\CommentRepository;
use Breeze\Repository\LikeRepository;
use Breeze\Repository\StatusRepository;
use Breeze\Repository\User\MoodRepository;
use Breeze\Repository\User\UserRepository;
use Breeze\Service\Actions\AdminService;
use Breeze\Service\ProfileService;
use Breeze\Util\Form\UserSettingsBuilder;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\Comment\ValidateComment;
use Breeze\Util\Validate\Validations\Likes\ValidateLikes;
use Breeze\Util\Validate\Validations\Status\ValidateStatus;

return [
	'controller.admin' => [
		'class' => AdminController::class,
		'arguments' => [
			AdminService::class,
			MoodRepository::class,
		],
	],
	'controller.wall' => [
		'class' => WallController::class,
		'arguments' => [
			Response::class,
			ProfileService::class,
		],
	],
	'controller.status' => [
		'class' => StatusController::class,
		'arguments' => [
			StatusRepository::class,
			UserRepository::class,
			ValidateStatus::class,
			Response::class,
		],
	],
	'controller.comment' => [
		'class' => CommentController::class,
		'arguments' => [
			CommentRepository::class,
			ValidateComment::class,
			Response::class,
		],
	],
	'controller.user.alerts' => [
		'class' => AlertsController::class,
		'arguments' => [
			ProfileService::class,
		],
	],
	'controller.user.settings' => [
		'class' => UserSettingsController::class,
		'arguments' => [
			UserRepository::class,
			Response::class,
			UserSettingsBuilder::class,
		],
	],
	'controller.likes' => [
		'class' => LikesController::class,
		'arguments' => [
			LikeRepository::class,
			ValidateLikes::class,
			Response::class,
		],
	],
];
