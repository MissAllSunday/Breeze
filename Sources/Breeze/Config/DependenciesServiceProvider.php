<?php

declare(strict_types=1);

namespace Breeze\Config;

use Breeze\Controller\AdminController;
use Breeze\Controller\API\CommentController;
use Breeze\Controller\API\LikesController;
use Breeze\Controller\API\StatusController;
use Breeze\Controller\User\Settings\UserSettingsController;
use Breeze\Controller\User\WallController;
use Breeze\Database\DatabaseClient;
use Breeze\Entity\AlertEntity;
use Breeze\Entity\CommentEntity;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\MemberEntity;
use Breeze\Entity\OptionsEntity;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Event\Comment\CommentEventListener;
use Breeze\Event\EventServiceProvider;
use Breeze\Event\HandlerServiceProvider;
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
use Breeze\Service\CommentService;
use Breeze\Service\LikeService;
use Breeze\Service\MentionService;
use Breeze\Service\PermissionsService;
use Breeze\Service\ProfileService;
use Breeze\Service\SecurityService;
use Breeze\Service\StatusService;
use Breeze\Service\WallVisibilityService;
use Breeze\Util\Components;
use Breeze\Util\Form\SettingsBuilder;
use Breeze\Util\Form\UserSettingsBuilder;
use Breeze\Util\Response;
use Breeze\Util\ResponseEmitter;
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
use Psr\Container\ContainerInterface;

/**
 * @codeCoverageIgnore
 */
class DependenciesServiceProvider extends AbstractServiceProvider
{
	protected const array DEPENDENCIES = [
		// Infrastructure - Shared (Singleton)
		DatabaseClient::class => ['arguments' => [], 'shared' => true],
		EventDispatcher::class => ['arguments' => [], 'shared' => true],

		// Utilities - Shared
		SettingsBuilder::class => ['arguments' => [], 'shared' => true],
		UserSettingsBuilder::class => ['arguments' => [], 'shared' => true],
		ResponseEmitter::class => ['arguments' => [], 'shared' => true],
		Response::class => ['arguments' => [SecurityService::class, ResponseEmitter::class], 'shared' => true],
		Components::class => ['arguments' => [], 'shared' => true],

		// Validation Types - Shared (Stateless validators)
		Data::class => ['arguments' => [], 'shared' => true],
		User::class => ['arguments' => [UserSettingsRepository::class], 'shared' => true],
		Allow::class => ['arguments' => [], 'shared' => true],

		// Validation Actions - New instances (Hold request-specific data)
		DeleteStatus::class => ['arguments' => [Data::class, User::class, Allow::class, StatusRepository::class]],
		PostStatus::class => ['arguments' => [Data::class, User::class, Allow::class, StatusRepository::class]],
		StatusByProfile::class => ['arguments' => [Data::class, User::class, Allow::class, StatusRepository::class]],
		DeleteComment::class => ['arguments' => [Data::class, User::class, Allow::class, CommentRepository::class, StatusRepository::class]],
		PostComment::class => ['arguments' => [Data::class, User::class, Allow::class, CommentRepository::class, StatusRepository::class]],
		Like::class => ['arguments' => [Data::class, User::class, Allow::class, LikeRepository::class]],

		// Composite Validators - New instances (Hold request state)
		ValidateStatus::class => ['arguments' => [DeleteStatus::class, PostStatus::class, StatusByProfile::class]],
		ValidateComment::class => ['arguments' => [DeleteComment::class, PostComment::class]],
		ValidateLikes::class => ['arguments' => [Like::class]],

		// Controllers - New instances (Handle per-request state)
		AdminController::class => ['arguments' => [AdminService::class, Response::class]],
		WallController::class => ['arguments' => [Response::class, ProfileService::class]],
		StatusController::class => ['arguments' => [
			StatusService::class,
			ValidateStatus::class,
			Response::class,
		]],
		CommentController::class => ['arguments' => [
			CommentService::class,
			ValidateComment::class,
			Response::class,
		]],
		LikesController::class => ['arguments' => [
			LikeService::class,
			ValidateLikes::class,
			Response::class,
		]],
		UserSettingsController::class => ['arguments' => [UserSettingsRepository::class, Response::class, UserSettingsBuilder::class, SecurityService::class]],

		// Entities - New instances (Data transfer objects)
		AlertEntity::class => ['arguments' => []],
		CommentEntity::class => ['arguments' => []],
		LikeEntity::class => ['arguments' => []],
		MemberEntity::class => ['arguments' => []],
		OptionsEntity::class => ['arguments' => []],
		SettingsEntity::class => ['arguments' => []],
		StatusEntity::class => ['arguments' => []],
		UserSettingsEntity::class => ['arguments' => []],

		// Event System - Shared
		EventServiceProvider::class => ['arguments' => [
			EventDispatcher::class,
			StatusEventListener::class,
			CommentEventListener::class,
			LikeEventListener::class,
		], 'shared' => true],
		StatusEventListener::class => ['arguments' => [AlertService::class], 'shared' => true],
		CommentEventListener::class => ['arguments' => [AlertService::class], 'shared' => true],
		LikeEventListener::class => ['arguments' => [AlertService::class, StatusRepository::class, CommentRepository::class], 'shared' => true],
		HandlerServiceProvider::class => ['arguments' => [AlertRepository::class], 'shared' => true],

		// Event Handlers - New instances (Created per event)
		StatusCreatedHandler::class => ['arguments' => [AlertEntity::class, AlertRepository::class]],

		// Repositories - Shared (Stateless data access)
		UserSettingsRepository::class => ['arguments' => [DatabaseClient::class, null], 'shared' => true],
		AlertRepository::class => ['arguments' => [DatabaseClient::class], 'shared' => true],
		CommentRepository::class => ['arguments' => [DatabaseClient::class, LikeRepository::class], 'shared' => true],
		LikeRepository::class => ['arguments' => [DatabaseClient::class], 'shared' => true],
		StatusRepository::class => ['arguments' => [DatabaseClient::class, CommentRepository::class, LikeRepository::class], 'shared' => true],

		// Services - Shared (Stateless business logic)
		SecurityService::class => ['arguments' => [], 'shared' => true],
		AdminService::class => ['arguments' => [SettingsBuilder::class, StatusService::class, CommentService::class, LikeService::class, UserSettingsRepository::class, Components::class, SecurityService::class], 'shared' => true],
		ProfileService::class => ['arguments' => [UserSettingsRepository::class, Components::class, PermissionsService::class, SecurityService::class], 'shared' => true],
		PermissionsService::class => ['arguments' => [], 'shared' => true],
		MentionService::class => ['arguments' => [UserSettingsRepository::class, AlertService::class], 'shared' => true],
		CommentService::class => ['arguments' => [CommentRepository::class, StatusRepository::class, EventServiceProvider::class, MentionService::class], 'shared' => true],
		StatusService::class => ['arguments' => [StatusRepository::class, UserSettingsRepository::class, PermissionsService::class, WallVisibilityService::class, EventServiceProvider::class, MentionService::class], 'shared' => true],
		LikeService::class => ['arguments' => [LikeRepository::class, EventServiceProvider::class], 'shared' => true],
		AlertService::class => ['arguments' => [AlertRepository::class, HandlerServiceProvider::class, UserSettingsRepository::class], 'shared' => true],
		WallVisibilityService::class => ['arguments' => [UserSettingsRepository::class, PermissionsService::class], 'shared' => true],
	];

	public function provides(string $id): bool
	{
		return in_array($id, array_keys(self::DEPENDENCIES));
	}

	public function register(): void
	{
		/** @var Container $container */
		$container = $this->getContainer();
		$container->add(ContainerInterface::class, $container);

		foreach (self::DEPENDENCIES as $service => $config) {
			// Determine if service should be shared (singleton) or new instance
			$isShared = isset($config['shared']) && $config['shared'] === true;
			$method = $isShared ? 'addShared' : 'add';

			$container->$method($service)
				->addArguments($config['arguments']);
		}
	}
}
