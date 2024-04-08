<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Repository\User\UserRepository;
use Breeze\Util\Permissions;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;

return [
	'type.data' => [
		'class' => Data::class,
	],
	'type.user' => [
		'class' => User::class,
		'arguments' => [UserRepository::class],
	],
	'type.allow' => [
		'class' => Allow::class,
		'arguments' => [Permissions::class],
	],
];
