<?php

return [
	'repo.mood' => [
		'class' => 'Breeze\Repository\User\MoodRepository',
		'arguments' => [
			'entity.mood',
			'model.mood'
		],
	],
];
