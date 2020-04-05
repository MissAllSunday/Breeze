<?php

declare(strict_types=1);

namespace Breeze\Config\Mapper;

use Breeze\Entity\AlertEntity;
use Breeze\Entity\CommentEntity;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\LogEntity;
use Breeze\Entity\MemberEntity;
use Breeze\Entity\MoodEntity;
use Breeze\Entity\NotificationEntity;
use Breeze\Entity\OptionsEntity;
use Breeze\Entity\StatusEntity;

return [
	'entity.alert' => [
		'class' => AlertEntity::class,
	],
	'entity.comment' => [
		'class' => CommentEntity::class,
	],
	'entity.like' => [
		'class' => LikeEntity::class,
	],
	'entity.log' => [
		'class' => LogEntity::class,
	],
	'entity.member' => [
		'class' => MemberEntity::class,
	],
	'entity.mood' => [
		'class' => MoodEntity::class,
	],
	'entity.notification' => [
		'class' => NotificationEntity::class,
	],
	'entity.options' => [
		'class' => OptionsEntity::class,
	],
	'entity.status' => [
		'class' => StatusEntity::class,
	],
];
