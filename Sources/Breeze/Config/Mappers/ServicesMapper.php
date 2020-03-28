<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Repository\AdminRepository;
use Breeze\Repository\User\MoodRepository;
use Breeze\Repository\User\UserRepository;
use Breeze\Service\AdminService;
use Breeze\Service\MoodService;
use Breeze\Service\PermissionsService;
use Breeze\Service\RequestService;
use Breeze\Service\UserService;
use Breeze\Service\WallService;

return [
	'service.request' => [
		'class' => RequestService::class
	],
	'service.mood' => [
		'class' => MoodService::class,
		'arguments'=> [MoodRepository::class, UserRepository::class]
	],
	'service.admin' => [
		'class' => AdminService::class,
		'arguments'=> [AdminRepository::class]
	],
	'service.user' => [
		'class' => UserService::class,
		'arguments'=> [UserRepository::class]
	],
	'service.permissions' => [
		'class' => PermissionsService::class,
		'arguments'=> [AdminRepository::class]
	],
	'service.wall' => [
		'class' => WallService::class,
		'arguments'=> [UserRepository::class]
	],
];
