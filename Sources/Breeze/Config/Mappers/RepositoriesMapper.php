<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Model\MoodModel;
use Breeze\Model\UserModel;
use Breeze\Repository\AdminRepository;
use Breeze\Repository\User\MoodRepository;
use Breeze\Repository\User\UserRepository;

return [
	'repo.user.mood' => [
		'class' => MoodRepository::class,
		'arguments'=> [MoodModel::class]
	],
	'repo.admin' => [
		'class' => AdminRepository::class,
		'arguments'=> [MoodModel::class]
	],
	'repo.user' => [
		'class' => UserRepository::class,
		'arguments'=> [UserModel::class]
	],
];
