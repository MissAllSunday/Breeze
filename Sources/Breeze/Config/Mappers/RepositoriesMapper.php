<?php


namespace Breeze\Config\Mapper;

use Breeze\Repository\AdminRepository;
use Breeze\Repository\User\MoodRepository;

return [
	'repo.user.mood' => [
		'class' => MoodRepository::class,
		'arguments'=> ['entity.mood', 'model.mood']
	],
	'repo.admin' => [
		'class' => AdminRepository::class,
		'arguments'=> ['entity.mood', 'model.mood']
	],
];