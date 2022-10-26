<?php

declare(strict_types=1);


namespace Breeze\Config\Mapper;

use Breeze\Repository\StatusRepository;
use Breeze\Repository\User\MoodRepository;
use Breeze\Util\Validate\Validations\Comment\DeleteComment;
use Breeze\Util\Validate\Validations\Comment\PostComment;
use Breeze\Util\Validate\Validations\Comment\ValidateComment;
use Breeze\Util\Validate\Validations\Likes\Like;
use Breeze\Util\Validate\Validations\Likes\ValidateLikes;
use Breeze\Util\Validate\Validations\Mood\DeleteMood;
use Breeze\Util\Validate\Validations\Mood\GetActiveMoods;
use Breeze\Util\Validate\Validations\Mood\GetAllMoods;
use Breeze\Util\Validate\Validations\Mood\PostMood;
use Breeze\Util\Validate\Validations\Mood\SetUserMood;
use Breeze\Util\Validate\Validations\Mood\ValidateMood;
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
		'arguments' => [Data::class, User::class, Allow::class, ValidateComment::class],
	],
	'validations.comment.postComment' => [
		'class' => PostComment::class,
		'arguments' => [Data::class, User::class, Allow::class, ValidateComment::class],
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
		'arguments' => [Data::class, User::class, Allow::class, ValidateLikes::class],
	],
	'validations.mood.validateMood' => [
		'class' => ValidateMood::class,
		'arguments' => [
			DeleteMood::class,
			GetActiveMoods::class,
			GetAllMoods::class,
			PostMood::class,
			SetUserMood::class,
		],
	],
	'validations.mood.deleteMood' => [
		'class' => DeleteMood::class,
		'arguments' => [Data::class, User::class, Allow::class, MoodRepository::class],
	],
	'validations.mood.getActive' => [
		'class' => GetActiveMoods::class,
		'arguments' => [Data::class, User::class, Allow::class, MoodRepository::class],
	],
	'validations.mood.getAll' => [
		'class' => GetAllMoods::class,
		'arguments' => [Data::class, User::class, Allow::class, MoodRepository::class],
	],
	'validations.mood.postMood' => [
		'class' => PostMood::class,
		'arguments' => [Data::class, User::class, Allow::class, MoodRepository::class],
	],
	'validations.mood.setUp' => [
		'class' => SetUserMood::class,
		'arguments' => [Data::class, User::class, Allow::class, MoodRepository::class],
	],
	'validations.status.validate' => [
		'class' => ValidateStatus::class,
		'arguments' => [DeleteStatus::class, PostStatus::class, StatusByProfile::class],
	],
	'validations.status.delete' => [
		'class' => DeleteStatus::class,
		'arguments' => [Data::class, User::class, Allow::class, StatusRepository::class],
	],
	'validations.status.post' => [
		'class' => PostStatus::class,
		'arguments' => [Data::class, User::class, Allow::class, StatusRepository::class],
	],
	'validations.status.byProfile' => [
		'class' => StatusByProfile::class,
		'arguments' => [Data::class, User::class, Allow::class, StatusRepository::class],
	],
	'validations.user.validate' => [
		'class' => ValidateUser::class,
		'arguments' => [UserSettings::class],
	],
	'validations.user.settings' => [
		'class' => UserSettings::class,
		'arguments' => [Data::class],
	],
];
