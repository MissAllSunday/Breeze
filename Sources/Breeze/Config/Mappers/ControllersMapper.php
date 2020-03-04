<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Controller\AdminController;

return [
    'controller.admin' => [
        'class' => AdminController::class,
        'arguments'=> ['service.request', 'service.admin']
    ]
];
