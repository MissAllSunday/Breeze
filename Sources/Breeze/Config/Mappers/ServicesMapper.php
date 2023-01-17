<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Repository\StatusRepository;
use Breeze\Repository\User\UserRepository;
use Breeze\Service\Actions\AdminService;
use Breeze\Service\Actions\WallService;
use Breeze\Service\PermissionsService;
use Breeze\Service\ProfileService;
use Breeze\Service\ValidateService;
use Breeze\Util\Components;
use Breeze\Util\Form\SettingsBuilder;

return [
	'service.admin' => [
		'class' => AdminService::class,
		'arguments' => [SettingsBuilder::class, Components::class],
	],
	'service.user' => [
		'class' => ProfileService::class,
		'arguments' => [UserRepository::class],
	],
	'service.permissions' => [
		'class' => PermissionsService::class,
	],
	'service.wall' => [
		'class' => WallService::class,
		'arguments' => [
			ProfileService::class,
			StatusRepository::class,
			Components::class,],
	],
	'service.validate' => [
		'class' => ValidateService::class,
		'arguments' => [ProfileService::class],
	],
];
