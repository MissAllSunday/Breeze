<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeInfoEntity;
use Breeze\Enums\LikesEnum;
use Breeze\Event\EventServiceProvider;
use Breeze\Event\Like\LikeCreatedEvent;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidLikeException;
use Breeze\Repository\LikeRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Traits\SettingsTrait;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\InvalidDataException;

class LikeService implements LikeServiceInterface
{
	use SettingsTrait;

	public function __construct(
		protected LikeRepositoryInterface $likeRepository,
		protected EventServiceProvider $eventServiceProvider,
		protected StatusRepositoryInterface $statusRepository,
		protected CommentRepositoryInterface $commentRepository,
		protected WallVisibilityServiceInterface $wallVisibilityService
	)
	{
	}

	/**
	 * The liker is always the session user, never a caller-supplied id, and
	 * the content must live on a wall the session user can access.
	 *
	 * @throws DataNotFoundException when the content does not exist or its wall is inaccessible
	 * @throws InvalidDataException
	 * @throws InvalidLikeException
	 */
	public function likeContent(LikesEnum $type, int $contentId): ?LikeInfoEntity
	{
		$userId = $this->sessionUserId();

		// Same 404 shape for "missing" and "not accessible" so existence is not leaked.
		if (!$this->wallVisibilityService->canAccessWall($this->resolveWallId($type, $contentId), $userId)) {
			throw new DataNotFoundException('error_no_data');
		}

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

	/**
	 * Wall owner of the liked content, read from persisted rows.
	 *
	 * @throws DataNotFoundException
	 */
	private function resolveWallId(LikesEnum $type, int $contentId): int
	{
		if ($type === LikesEnum::Comments) {
			$statusId = $this->commentRepository->getById($contentId)->getStatusId();

			return $this->statusRepository->getBasicInfoById($statusId)->getWallId();
		}

		return $this->statusRepository->getBasicInfoById($contentId)->getWallId();
	}

	private function sessionUserId(): int
	{
		$userInfo = $this->global('user_info');

		return (int) ($userInfo['id'] ?? 0);
	}
}
