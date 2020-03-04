<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Service\AdminService;
use Breeze\Service\MoodService;

return [
	'service.mood' => [
		'class' => MoodService::class,
		'arguments'=> ['repository.mood']
	],
	'service.admin' => [
		'class' => AdminService::class,
		'arguments'=> ['repository.admin', 'service.mood']
	],
];
