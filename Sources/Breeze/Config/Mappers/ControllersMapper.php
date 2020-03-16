<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Controller\AdminController;
use Breeze\Service\AdminService;
use Breeze\Service\MoodService;
use Breeze\Service\RequestService;

return [
	'controller.admin' => [
		'class' => AdminController::class,
		'arguments'=> [RequestService::class, AdminService::class, MoodService::class]
	]
];
