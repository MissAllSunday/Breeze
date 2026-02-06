<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeInfoEntity;
use Breeze\LikesEnum;

interface LikeRepositoryInterface extends BaseRepositoryInterface
{
	/**
	 * @param array $contentIds [int]
	 * @return array [LikeInfoEntity]
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
	public function insert(LikeEntity $likeEntity): LikeInfoEntity;

	public function count(LikeEntity $likeEntity): int;

	/**
	 *
	 * @return array [HandledEntityInterface]
	 */

	/**
	 * @param array $likeEntities [LikeEntity]
	 */
	public function buildLikeInfo(array $likeEntities, LikesEnum $type, int $contentId): LikeInfoEntity;

	/**
	 * @throws InvalidLikeException
	 * @throws InvalidDataException
	 */
	public function likeContent(LikesEnum $type, int $contentId, int $userId): ?LikeInfoEntity;

	public function countOrphans(): int;

	public function deleteOrphans(): void;
}
