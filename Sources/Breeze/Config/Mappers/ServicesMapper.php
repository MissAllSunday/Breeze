<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Repository\User\UserRepository;
use Breeze\Service\Actions\AdminService;
use Breeze\Service\EditorService;
use Breeze\Service\PermissionsService;
use Breeze\Service\ProfileService;
use Breeze\Service\ValidateService;
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
	'service.editor' => [
		'class' => EditorService::class,
	],
	'service.validate' => [
		'class' => ValidateService::class,
		'arguments' => [ProfileService::class],
	],
];
