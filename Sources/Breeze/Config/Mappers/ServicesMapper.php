<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Repository\AdminRepository;
use Breeze\Repository\User\MoodRepository;
use Breeze\Service\AdminService;
use Breeze\Service\MoodService;
use Breeze\Service\RequestService;

return [
    'service.request' => [
        'class' => RequestService::class
    ],
    'service.mood' => [
        'class' => MoodService::class,
        'arguments'=> [MoodRepository::class]
    ],
    'service.admin' => [
        'class' => AdminService::class,
        'arguments'=> [AdminRepository::class, MoodService::class]
    ],
];
