<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeInfoEntity;
use Breeze\Event\EventServiceProvider;
use Breeze\Event\Like\LikeCreatedEvent;
use Breeze\LikesEnum;
use Breeze\Repository\InvalidDataException;
use Breeze\Repository\InvalidLikeException;
use Breeze\Repository\LikeRepositoryInterface;

class LikeService implements LikeServiceInterface
{
	public function __construct(
		protected LikeRepositoryInterface $likeRepository,
		protected EventServiceProvider $eventServiceProvider
	)
	{
	}

	/**
	 * @throws InvalidDataException
	 * @throws InvalidLikeException
	 */
	public function likeContent(LikesEnum $type, int $contentId, int $userId): ?LikeInfoEntity
	{
		$likeEntity = LikeEntity::from([
			LikeEntity::TYPE => $type->value,
			LikeEntity::ID => $contentId,
			LikeEntity::ID_MEMBER => $userId,
		]);
		$likeInfo = $this->likeRepository->likeContent($likeEntity);

		// Dispatch the like created event only when a new like is created
		if (!$likeInfo->isAlreadyLiked()) {
			$this->eventServiceProvider->getDispatcher()->dispatch(new LikeCreatedEvent($likeEntity));
		}

		return $likeInfo;
	}

	public function countOrphans(): int
	{
		return $this->likeRepository->countOrphans();
	}

	public function deleteOrphans(): void
	{
		$this->likeRepository->deleteOrphans();
	}
}
