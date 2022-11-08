<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Repository\BaseRepository;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;

return [
	'type.data' => [
		'class' => Data::class,
	],
	'type.user' => [
		'class' => User::class,
		'arguments' => [BaseRepository::class],
	],
	'type.allow' => [
		'class' => Allow::class,
	],
];
