<?php

declare(strict_types=1);

namespace Breeze\Config\Mapper;

$mapper =[];
$models = [
    'alert',
    'comment',
    'like',
    'log',
    'member',
    'mood',
    'notification',
    'options',
    'status',
];

foreach ($models as $model)
	$mapper['model.' . $model] = [
	    'class' => 'Breeze\Model\User\\' . ucfirst($model) . 'Model',
	    'arguments'=> ['Breeze\Database\DatabaseClient\\']
	];

return $mapper;
