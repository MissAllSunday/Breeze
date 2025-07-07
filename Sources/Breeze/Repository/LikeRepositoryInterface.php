<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeHandledEntity;
use Breeze\LikesEnum;

interface LikeRepositoryInterface extends BaseRepositoryInterface
{
	public function getLikeInfo(LikesEnum $type, int $contentId): array;

	/**
	 * @param array $contentIds [int]
	 * @return array [LikeHandledEntity]
	 */
	public function getByContent(LikesEnum $type, array $contentIds): array;

	public function isContentAlreadyLiked(LikeEntity $likeEntity): bool;

	/**
	 * @throws InvalidLikeException
	 */
	public function deleteByContent(LikeEntity $likeEntity): void;

	/**
	 * @throws InvalidLikeException
	 */
	public function insert(LikeEntity $likeEntity): LikeHandledEntity;

	public function count(LikeEntity $likeEntity): int;

	/**
	 *
	 * @return array [HandledEntityInterface]
	 */

	/**
	 * @param array $likeData [LikeHandledEntity]
	 */
	public function buildLikeData(array $likeData, int $likesCount): LikeHandledEntity;

	public function likeContent(LikesEnum $type, int $contentId, int $userId): LikeHandledEntity;
}
