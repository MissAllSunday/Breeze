<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Repository\CommentRepository;
use Breeze\Repository\LikeRepository;
use Breeze\Repository\StatusRepository;
use Breeze\Util\Validate\Validations\Comment\DeleteComment;
use Breeze\Util\Validate\Validations\Comment\PostComment;
use Breeze\Util\Validate\Validations\Comment\ValidateComment;
use Breeze\Util\Validate\Validations\Likes\Like;
use Breeze\Util\Validate\Validations\Likes\ValidateLikes;
use Breeze\Util\Validate\Validations\Status\DeleteStatus;
use Breeze\Util\Validate\Validations\Status\PostStatus;
use Breeze\Util\Validate\Validations\Status\StatusByProfile;
use Breeze\Util\Validate\Validations\Status\ValidateStatus;
use Breeze\Util\Validate\Validations\User\UserSettings;
use Breeze\Util\Validate\Validations\User\ValidateUser;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;

return [
	'validations.comment.deleteComment' => [
		'class' => DeleteComment::class,
		'arguments' => [Data::class, User::class, Allow::class, CommentRepository::class],
	],
	'validations.comment.postComment' => [
		'class' => PostComment::class,
		'arguments' => [Data::class, User::class, Allow::class, CommentRepository::class],
	],
	'validations.comment.validateComment' => [
		'class' => ValidateComment::class,
		'arguments' => [DeleteComment::class, PostComment::class],
	],
	'validations.likes.validateLikes' => [
		'class' => ValidateLikes::class,
		'arguments' => [Like::class],
	],
	'validations.likes.like' => [
		'class' => Like::class,
		'arguments' => [Data::class, User::class, Allow::class, LikeRepository::class],
	],
	'validations.user.validate' => [
		'class' => ValidateUser::class,
		'arguments' => [UserSettings::class],
	],
	'validations.user.settings' => [
		'class' => UserSettings::class,
		'arguments' => [Data::class],
	],
	'validations.status.validate' => [
		'class' => ValidateStatus::class,
		'arguments' => [DeleteStatus::class, PostStatus::class, StatusByProfile::class],
	],
	'validations.status.post' => [
		'class' => PostStatus::class,
		'arguments' => [Data::class, User::class, Allow::class, StatusRepository::class],
	],
	'validations.status.byProfile' => [
		'class' => StatusByProfile::class,
		'arguments' => [Data::class, User::class, Allow::class, StatusRepository::class],
	],
	'validations.status.delete' => [
		'class' => DeleteStatus::class,
		'arguments' => [Data::class, User::class, Allow::class, StatusRepository::class],
	],
];
