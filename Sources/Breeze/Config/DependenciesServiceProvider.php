<?php

declare(strict_types=1);

namespace Breeze\Config;

use Breeze\Controller\AdminController;
use Breeze\Controller\API\CommentController;
use Breeze\Controller\API\LikesController;
use Breeze\Controller\API\StatusController;
use Breeze\Controller\BuddyController;
use Breeze\Controller\User\Settings\UserSettingsController;
use Breeze\Controller\User\WallController;
use Breeze\Database\DatabaseClient;
use Breeze\Entity\AlertEntity;
use Breeze\Entity\AlertHandledEntity;
use Breeze\Entity\CommentEntity;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\LogEntity;
use Breeze\Entity\MemberEntity;
use Breeze\Entity\MentionEntity;
use Breeze\Entity\NotificationEntity;
use Breeze\Entity\OptionsEntity;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Event\Comment\CommentEventListener;
use Breeze\Event\EventServiceProvider;
use Breeze\Event\Like\LikeEventListener;
use Breeze\Event\Status\StatusCreatedHandler;
use Breeze\Event\Status\StatusEventListener;
use Breeze\Repository\AlertRepository;
use Breeze\Repository\CommentRepository;
use Breeze\Repository\LikeRepository;
use Breeze\Repository\StatusRepository;
use Breeze\Repository\User\SettingsRepository as UserSettingsRepository;
use Breeze\Service\Actions\AdminService;
use Breeze\Service\AlertService;
use Breeze\Service\PermissionsService;
use Breeze\Service\ProfileService;
use Breeze\Service\StatusService;
use Breeze\Util\Components;
use Breeze\Util\Form\SettingsBuilder;
use Breeze\Util\Form\UserSettingsBuilder;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\Comment\DeleteComment;
use Breeze\Util\Validate\Validations\Comment\PostComment;
use Breeze\Util\Validate\Validations\Comment\ValidateComment;
use Breeze\Util\Validate\Validations\Likes\Like;
use Breeze\Util\Validate\Validations\Likes\ValidateLikes;
use Breeze\Util\Validate\Validations\Status\DeleteStatus;
use Breeze\Util\Validate\Validations\Status\PostStatus;
use Breeze\Util\Validate\Validations\Status\StatusByProfile;
use Breeze\Util\Validate\Validations\Status\ValidateStatus;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Event\EventDispatcher;

class DependenciesServiceProvider extends AbstractServiceProvider
{
	protected const array DEPENDENCIES = [
		DatabaseClient::class => [],
		SettingsBuilder::class => [],
		UserSettingsBuilder::class => [],
		Response::class => [],
		Components::class => [],
		Data::class => [],
		User::class => [UserSettingsRepository::class],
		Allow::class => [],
		DeleteStatus::class => [Data::class, User::class, Allow::class, StatusRepository::class],
		PostStatus::class => [Data::class, User::class, Allow::class, StatusRepository::class],
		StatusByProfile::class => [Data::class, User::class, Allow::class, StatusRepository::class],
		DeleteComment::class => [Data::class, User::class, Allow::class, CommentRepository::class],
		PostComment::class => [Data::class, User::class, Allow::class, CommentRepository::class],
		Like::class => [Data::class, User::class, Allow::class, LikeRepository::class],
		ValidateStatus::class => [DeleteStatus::class, PostStatus::class, StatusByProfile::class],
		ValidateComment::class => [DeleteComment::class, PostComment::class],
		ValidateLikes::class => [Like::class],
		AdminController::class => [AdminService::class, Response::class],
		WallController::class => [Response::class, ProfileService::class],
		StatusController::class => [
			StatusService::class,
			ValidateStatus::class,
			Response::class,
			EventServiceProvider::class,
		],
		CommentController::class => [
			CommentRepository::class,
			ValidateComment::class,
			Response::class,
			EventServiceProvider::class,
		],
		LikesController::class => [
			LikeRepository::class,
			ValidateLikes::class,
			Response::class,
			EventServiceProvider::class,
		],
		UserSettingsController::class => [UserSettingsRepository::class, Response::class, UserSettingsBuilder::class],
		BuddyController::class => [Response::class, ProfileService::class],
		AlertEntity::class => [],
		CommentEntity::class => [],
		LikeEntity::class => [],
		LogEntity::class => [],
		MemberEntity::class => [],
		MentionEntity::class => [],
		NotificationEntity::class => [],
		OptionsEntity::class => [],
		SettingsEntity::class => [],
		StatusEntity::class => [],
		UserSettingsEntity::class => [],
		StatusEventListener::class => [AlertService::class],
		EventDispatcher::class => [],
		EventServiceProvider::class => [EventDispatcher::class, StatusEventListener::class, CommentEventListener::class, LikeEventListener::class],
		StatusCreatedHandler::class => [AlertHandledEntity::class],
		UserSettingsRepository::class => [DatabaseClient::class],
		AlertRepository::class => [DatabaseClient::class],
		CommentRepository::class => [DatabaseClient::class, LikeRepository::class],
		LikeRepository::class => [DatabaseClient::class],
		StatusRepository::class => [DatabaseClient::class, CommentRepository::class, LikeRepository::class],
		AdminService::class => [SettingsBuilder::class],
		ProfileService::class => [UserSettingsRepository::class, Components::class, PermissionsService::class],
		PermissionsService::class => [],
		StatusService::class => [StatusRepository::class, UserSettingsRepository::class, PermissionsService::class],
		AlertService::class => [AlertRepository::class],
		CommentEventListener::class => [AlertService::class],
		LikeEventListener::class => [AlertService::class, StatusRepository::class, CommentRepository::class],
	];

	public function provides(string $id): bool
	{
		return in_array($id, array_keys(self::DEPENDENCIES));
	}

	public function register(): void
	{
		/** @var Container $container */
		$container = $this->getContainer();

		foreach (self::DEPENDENCIES as $service => $arguments) {
			$container->add($service)->addArguments($arguments);
		}
	}
}
