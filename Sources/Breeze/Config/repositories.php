<?php

declare(strict_types=1);

return [
    'repo.mood' => [
        'class' => 'Breeze\Repository\User\MoodRepository',
        'arguments' => [
            'entity.mood',
            'model.mood'
        ],
    ],
];
