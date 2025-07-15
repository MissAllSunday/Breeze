<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\LikeEntity;
use Breeze\LikesEnum;

interface LikeRepositoryInterface extends BaseRepositoryInterface
{
	/**
	 * @param array $contentIds [int]
	 * @return array [LikeEntity]
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
	public function insert(LikeEntity $likeEntity): LikeEntity;

	public function count(LikeEntity $likeEntity): int;

	/**
	 *
	 * @return array [HandledEntityInterface]
	 */

	/**
	 * @param array $likeData [LikeEntity]
	 */
	public function buildLikeData(array $likeData, int $likesCount): LikeEntity;

	/**
	 * @throws InvalidLikeException
	 * @throws InvalidDataException
	 * @return array [LikeEntity]
	 */
	public function likeContent(LikesEnum $type, int $contentId, int $userId): array;
}
