<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Database\DatabaseClient;

return [
	'client.db' => [
		'class' => DatabaseClient::class,
	],
];
