<?php


namespace Breeze\Config\Mapper;

return [
	'repo.user.mood' => [
		'class' => 'Breeze\Repository\User\MoodRepository',
		'arguments'=> ['Breeze\Database\DatabaseClient\\']
	],
];