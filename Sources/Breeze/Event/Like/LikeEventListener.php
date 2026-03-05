<?php

declare(strict_types=1);

namespace Breeze\Event\Like;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Entity\LikeEntity;
use Breeze\Entity\SharedEntityInterface;
use Breeze\LikesEnum;
use Breeze\Repository\CommentRepository;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepository;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Service\AlertServiceInterface;
use Breeze\Traits\TextTrait;
use Psr\Container\ContainerInterface;

class LikeEventListener
{
	use TextTrait;

	protected const string CONTENT_TYPE = Breeze::PATTERN . 'like';
	protected const string CONTENT_ACTION_CREATED = Breeze::PATTERN . 'created';

	public function __construct(
		protected readonly AlertServiceInterface $alertService,
		protected readonly ContainerInterface $container
	) {
	}

	public function onLikeCreated(LikeCreatedEvent $event): void
	{
		$likeEntity = $event->getLikeEntity();
		$contentId = $likeEntity->getContentId();
		$contentType = $likeEntity->getContentType();
		$userId = $likeEntity->getIdMember();

		$content = $this->getContent($likeEntity);
		$contentOwnerId = $content->getUserId();

		// Don't send alert if the user is liking their own content
		if ($userId === $contentOwnerId) {
			return;
		}

		$this->alertService->send(AlertEntity::from([
			AlertEntity::ID_MEMBER => $contentOwnerId,
			AlertEntity::ID_MEMBER_STARTED => $userId,
			AlertEntity::CONTENT_TYPE => self::CONTENT_TYPE,
			AlertEntity::CONTENT_ID => $content->getId(),
			AlertEntity::CONTENT_ACTION => self::CONTENT_ACTION_CREATED,
			AlertEntity::IS_READ => 0,
			AlertEntity::EXTRA => [
				'content_id' => $contentId,
				'content_type' => $contentType,
			],
		]));
	}

	protected function getContent(LikeEntity $likeEntity): SharedEntityInterface
	{
		/** @var StatusRepositoryInterface|CommentRepositoryInterface $repository */
		$repository = match ($likeEntity->getContentType()) {
			LikesEnum::Status => $this->container->get(StatusRepository::class),
			LikesEnum::Comments => $this->container->get(CommentRepository::class),
		};

		return $repository->getById($likeEntity->getContentId());
	}
}
