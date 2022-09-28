<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Model\CommentModel;
use Breeze\Model\LikeModel;
use Breeze\Model\MoodModel;
use Breeze\Model\StatusModel;
use Breeze\Model\UserModel;
use Breeze\Repository\CommentRepository;
use Breeze\Repository\LikeRepository;
use Breeze\Repository\StatusRepository;
use Breeze\Repository\User\MoodRepository;
use Breeze\Repository\User\UserRepository;

return [
	'repo.user.mood' => [
		'class' => MoodRepository::class,
		'arguments'=> [MoodModel::class],
	],
	'repo.user' => [
		'class' => UserRepository::class,
		'arguments'=> [UserModel::class],
	],
	'repo.status' => [
		'class' => StatusRepository::class,
		'arguments'=> [StatusModel::class, CommentRepository::class, LikeRepository::class],
	],
	'repo.comment' => [
		'class' => CommentRepository::class,
		'arguments'=> [CommentModel::class, LikeRepository::class],
	],
	'repo.like' => [
		'class' => LikeRepository::class,
		'arguments'=> [LikeModel::class],
	],
];
