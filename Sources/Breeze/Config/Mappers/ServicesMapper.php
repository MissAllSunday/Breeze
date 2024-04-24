<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Repository\StatusRepository;
use Breeze\Repository\User\UserRepository;
use Breeze\Service\Actions\AdminService;
use Breeze\Service\PermissionsService;
use Breeze\Service\ProfileService;
use Breeze\Service\StatusService;
use Breeze\Util\Components;
use Breeze\Util\Form\SettingsBuilder;

return [
	'service.admin' => [
		'class' => AdminService::class,
		'arguments' => [SettingsBuilder::class],
	],
	'service.profile' => [
		'class' => ProfileService::class,
		'arguments' => [UserRepository::class, Components::class],
	],
	'service.permissions' => [
		'class' => PermissionsService::class,
	],
	'service.status' => [
		'class' => StatusService::class,
		'arguments' => [StatusRepository::class, UserRepository::class, PermissionsService::class],
	],
];
