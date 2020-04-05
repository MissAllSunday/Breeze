<?php

declare(strict_types=1);

namespace Breeze\Config\Mapper;

use Breeze\Entity\AlertBaseEntity;
use Breeze\Entity\CommentBaseEntity;
use Breeze\Entity\LikeBaseEntity;
use Breeze\Entity\LogBaseEntity;
use Breeze\Entity\MemberBaseEntity;
use Breeze\Entity\MoodBaseEntity;
use Breeze\Entity\NotificationBaseEntity;
use Breeze\Entity\OptionsBaseEntity;
use Breeze\Entity\StatusBaseEntity;

return [
	'entity.alert' => [
		'class' => AlertBaseEntity::class,
	],
	'entity.comment' => [
		'class' => CommentBaseEntity::class,
	],
	'entity.like' => [
		'class' => LikeBaseEntity::class,
	],
	'entity.log' => [
		'class' => LogBaseEntity::class,
	],
	'entity.member' => [
		'class' => MemberBaseEntity::class,
	],
	'entity.mood' => [
		'class' => MoodBaseEntity::class,
	],
	'entity.notification' => [
		'class' => NotificationBaseEntity::class,
	],
	'entity.options' => [
		'class' => OptionsBaseEntity::class,
	],
	'entity.status' => [
		'class' => StatusBaseEntity::class,
	],
];
