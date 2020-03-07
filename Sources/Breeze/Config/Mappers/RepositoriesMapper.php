<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Entity\MoodEntity;
use Breeze\Model\MoodModel;
use Breeze\Repository\AdminRepository;
use Breeze\Repository\User\MoodRepository;

return [
    'repo.user.mood' => [
        'class' => MoodRepository::class,
        'arguments'=> [MoodEntity::class, MoodModel::class]
    ],
    'repo.admin' => [
        'class' => AdminRepository::class,
        'arguments'=> [MoodEntity::class, MoodModel::class]
    ],
];
