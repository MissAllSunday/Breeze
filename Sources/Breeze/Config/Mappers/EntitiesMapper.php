<?php

declare(strict_types=1);

namespace Breeze\Config\Mapper;

$mapper =[];
$entities = [
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

foreach ($entities as $entity)
	$mapper['entity.' . $entity] = [
	    'class' => 'Breeze\Entity\\' . ucfirst($entity) . 'Entity',
	];

return $mapper;
