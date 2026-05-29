<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeInfoEntity;
use Breeze\Enums\LikesEnum;
use Breeze\Event\EventServiceProvider;
use Breeze\Event\Like\LikeCreatedEvent;
use Breeze\Repository\InvalidLikeException;
use Breeze\Repository\LikeRepositoryInterface;
use Breeze\Util\Validate\InvalidDataException;

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

		$this->eventServiceProvider->getDispatcher()->dispatch(new LikeCreatedEvent($likeEntity));

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
