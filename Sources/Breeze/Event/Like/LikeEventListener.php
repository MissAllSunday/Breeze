<?php

declare(strict_types=1);

namespace Breeze\Event\Like;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Entity\HandledEntityInterface;
use Breeze\Entity\LikeHandledEntity;
use Breeze\LikesEnum;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Service\AlertServiceInterface;
use Breeze\Traits\TextTrait;

class LikeEventListener
{
	use TextTrait;

	protected const string CONTENT_TYPE = Breeze::PATTERN . 'like';
	protected const string CONTENT_ACTION_CREATED = Breeze::PATTERN . 'created';

	public function __construct(
		protected readonly AlertServiceInterface $alertService,
		protected readonly StatusRepositoryInterface $statusRepository,
		protected readonly CommentRepositoryInterface $commentRepository
	) {
	}

	public function onLikeCreated(LikeCreatedEvent $event): void
	{
		$handledLike = $event->getLikeHandledEntity();
		$contentId = $handledLike->getContentId();
		$contentType = $handledLike->getContentType();
		$userId = $handledLike->getIdMember();

		$content = $this->getContent($handledLike);

		$this->alertService->send(new AlertEntity([
			AlertEntity::COLUMN_ID_MEMBER => $content->getUserId(),
			AlertEntity::COLUMN_ID_MEMBER_STARTED => $userId,
			AlertEntity::COLUMN_CONTENT_TYPE => self::CONTENT_TYPE,
			AlertEntity::COLUMN_CONTENT_ID => $content->getId(),
			AlertEntity::COLUMN_CONTENT_ACTION => self::CONTENT_ACTION_CREATED,
			AlertEntity::COLUMN_IS_READ => 0,
			AlertEntity::COLUMN_EXTRA => [
				'content_id' => $contentId,
				'content_type' => $contentType,
			],
		]));
	}

	protected function getContent(LikeHandledEntity $handledLike): HandledEntityInterface
	{
		/** @var StatusRepositoryInterface|CommentRepositoryInterface $repository */
		$repository = match ($handledLike->getContentType()) {
			LikesEnum::Status->value => $this->statusRepository,
			LikesEnum::Comments->value => $this->commentRepository,
			default => throw new \InvalidArgumentException('Invalid content type'),
		};

		return $repository->getById($handledLike->getContentId());
	}
}
