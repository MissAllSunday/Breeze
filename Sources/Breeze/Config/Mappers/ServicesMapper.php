<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Repository\CommentRepository;
use Breeze\Repository\StatusRepository;
use Breeze\Repository\User\MoodRepository;
use Breeze\Repository\User\UserRepository;
use Breeze\Service\Actions\AdminService;
use Breeze\Service\Actions\UserSettingsService;
use Breeze\Service\Actions\WallService;
use Breeze\Service\FormService;
use Breeze\Service\MoodService;
use Breeze\Service\PermissionsService;
use Breeze\Service\StatusService;
use Breeze\Service\UserService;
use Breeze\Service\ValidateService;

return [
	'service.mood' => [
		'class' => MoodService::class,
		'arguments'=> [MoodRepository::class, UserRepository::class]
	],
	'service.admin' => [
		'class' => AdminService::class,
		'arguments'=> [FormService::class]
	],
	'service.user' => [
		'class' => UserService::class,
		'arguments'=> [UserRepository::class]
	],
	'service.user.settings' => [
		'class' => UserSettingsService::class,
		'arguments'=> [UserRepository::class]
	],
	'service.permissions' => [
		'class' => PermissionsService::class,
	],
	'service.wall' => [
		'class' => WallService::class,
		'arguments'=> [UserService::class, StatusRepository::class, CommentRepository::class]
	],
	'service.status' => [
		'class' => StatusService::class,
		'arguments'=> [StatusRepository::class]
	],
	'service.form' => [
		'class' => FormService::class,
	],
	'service.validate' => [
		'class' => ValidateService::class,
	],
];
