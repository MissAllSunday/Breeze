<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Repository\CommentRepository;
use Breeze\Repository\LikeRepository;
use Breeze\Repository\StatusRepository;
use Breeze\Repository\User\MoodRepository;
use Breeze\Repository\User\UserRepository;
use Breeze\Service\Actions\AdminService;
use Breeze\Service\Actions\UserSettingsService;
use Breeze\Service\Actions\WallService;
use Breeze\Service\CommentService;
use Breeze\Service\LikeService;
use Breeze\Service\MoodService;
use Breeze\Service\PermissionsService;
use Breeze\Service\StatusService;
use Breeze\Service\UserService;
use Breeze\Service\ValidateService;
use Breeze\Util\Components;
use Breeze\Util\Form\SettingsBuilder;
use Breeze\Util\Form\UserSettingsBuilder;

return [
	'service.mood' => [
		'class' => MoodService::class,
		'arguments'=> [MoodRepository::class, UserRepository::class, Components::class],
	],
	'service.admin' => [
		'class' => AdminService::class,
		'arguments'=> [SettingsBuilder::class, Components::class],
	],
	'service.user' => [
		'class' => UserService::class,
		'arguments'=> [UserRepository::class],
	],
	'service.user.settings' => [
		'class' => UserSettingsService::class,
		'arguments'=> [
			UserRepository::class,
			Components::class,
			UserSettingsBuilder::class, ],
	],
	'service.permissions' => [
		'class' => PermissionsService::class,
	],
	'service.wall' => [
		'class' => WallService::class,
		'arguments'=> [
			UserService::class,
			StatusRepository::class,
			CommentRepository::class,
			Components::class, ],
	],
	'service.status' => [
		'class' => StatusService::class,
		'arguments'=> [UserService::class, StatusRepository::class, CommentRepository::class, LikeService::class],
	],
	'service.comment' => [
		'class' => CommentService::class,
		'arguments'=> [UserService::class, StatusRepository::class, CommentRepository::class],
	],
	'service.validate' => [
		'class' => ValidateService::class,
		'arguments'=> [UserService::class],
	],
	'service.likes' => [
		'class' => LikeService::class,
		'arguments'=> [LikeRepository::class],
	],
];
