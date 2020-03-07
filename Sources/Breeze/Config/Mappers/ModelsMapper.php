<?php

declare(strict_types=1);

namespace Breeze\Config\Mapper;

use Breeze\Database\DatabaseClient;
use Breeze\Model\AlertModel;
use Breeze\Model\CommentModel;
use Breeze\Model\LikeModel;
use Breeze\Model\LogModel;
use Breeze\Model\MentionModel;
use Breeze\Model\MoodModel;
use Breeze\Model\NotificationModel;
use Breeze\Model\StatusModel;
use Breeze\Model\UserModel;


return [
    'model.mood' => [
        'class' => MoodModel::class,
        'arguments'=> [DatabaseClient::class]
    ],
	'model.alert' => [
		'class' => AlertModel::class,
		'arguments'=> [DatabaseClient::class]
	],
	'model.comment' => [
		'class' => CommentModel::class,
		'arguments'=> [DatabaseClient::class]
	],
	'model.like' => [
		'class' => LikeModel::class,
		'arguments'=> [DatabaseClient::class]
	],
	'model.log' => [
		'class' => LogModel::class,
		'arguments'=> [DatabaseClient::class]
	],
	'model.mention' => [
		'class' => MentionModel::class,
		'arguments'=> [DatabaseClient::class]
	],
	'model.notification' => [
		'class' => NotificationModel::class,
		'arguments'=> [DatabaseClient::class]
	],
	'model.status' => [
		'class' => StatusModel::class,
		'arguments'=> [DatabaseClient::class]
	],
	'model.user' => [
		'class' => UserModel::class,
		'arguments'=> [DatabaseClient::class]
	],
];
