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
use Breeze\Event\EventServiceProvider;
use Breeze\Event\Status\StatusEventListener;
use Breeze\Model\AlertModel;
use Breeze\Model\CommentModel;
use Breeze\Model\LikeModel;
use Breeze\Model\LogModel;
use Breeze\Model\MentionModel;
use Breeze\Model\NotificationModel;
use Breeze\Model\StatusModel;
use Breeze\Model\UserModel;
use Breeze\Repository\CommentRepository;
use Breeze\Repository\LikeRepository;
use Breeze\Repository\NotificationRepository;
use Breeze\Repository\StatusRepository;
use Breeze\Repository\User\UserRepository;
use Breeze\Service\Actions\AdminService;
use Breeze\Service\PermissionsService;
use Breeze\Service\ProfileService;
use Breeze\Service\StatusService;
use Breeze\Util\Components;
use Breeze\Util\Form\SettingsBuilder;
use Breeze\Util\Form\UserSettingsBuilder;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\Comment\ValidateComment;
use Breeze\Util\Validate\Validations\Likes\ValidateLikes;
use Breeze\Util\Validate\Validations\Status\ValidateStatus;
use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Event\EventDispatcher;

class ConfigServiceProvider extends AbstractServiceProvider
{
	protected const SERVICES = [
		DatabaseClient::class => [],
		SettingsBuilder::class => [],
		UserSettingsBuilder::class => [],
		Response::class => [],
		Components::class => [],
		AdminController::class => [AdminService::class],
		WallController::class => [Response::class, ProfileService::class],
		StatusController::class => [StatusService::class, ValidateStatus::class, Response::class, EventServiceProvider::class],
		CommentController::class => [CommentRepository::class, ValidateComment::class, Response::class],
		LikesController::class => [LikeRepository::class, ValidateLikes::class, Response::class],
		UserSettingsController::class => [UserRepository::class, Response::class, UserSettingsBuilder::class],
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
		EventServiceProvider::class => [EventDispatcher::class, StatusEventListener::class],
		AlertModel::class => [],
		CommentModel::class => [DatabaseClient::class],
		LikeModel::class => [DatabaseClient::class],
		LogModel::class => [DatabaseClient::class],
		MentionModel::class => [DatabaseClient::class],
		NotificationModel::class => [DatabaseClient::class],
		StatusModel::class => [DatabaseClient::class],
		UserModel::class => [DatabaseClient::class],
		UserRepository::class => [UserModel::class],
		CommentRepository::class => [CommentModel::class, LikeRepository::class],
		LikeRepository::class => [LikeModel::class],
		NotificationRepository::class => [NotificationModel::class],
		StatusRepository::class => [StatusModel::class, CommentRepository::class, LikeRepository::class],
		AdminService::class => [SettingsBuilder::class],
		ProfileService::class => [UserRepository::class, Components::class, PermissionsService::class],
		PermissionsService::class => [],
		StatusService::class => [StatusRepository::class, UserRepository::class, PermissionsService::class],
	];

	public function provides(string $id): bool
	{
		return in_array($id, array_keys(self::SERVICES));
	}

	public function register(): void
	{
		/** @var Container $container */
		$container = $this->getContainer();

		foreach (self::SERVICES as $service => $arguments) {
			if (!empty($arguments)) {
				$container->add($service)->addArguments($arguments);
			} else {
				$container->add($service);
			}
		}
	}
}
