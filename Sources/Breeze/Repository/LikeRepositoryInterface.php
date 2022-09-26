<?php

declare(strict_types=1);


namespace Breeze\Repository;

interface LikeRepositoryInterface extends BaseRepositoryInterface
{
	public function getByContent(string $type, int $contentId): array;

	public function isContentAlreadyLiked(string $type, int $contentId, int $userId): bool;

	/**
	 * @throws InvalidLikeException
	 */
	public function delete(string $type, int $contentId, int $userId): void;

	/**
	 * @throws InvalidLikeException
	 */
	public function insert(string $type, int $contentId, int $userId): void;

	public function count(string $type, int $contentId): int;

	public function appendLikeData(array $items, string $itemIdName) : array;

	public function buildLikeData(
		?string $type,
		?int $contentId,
		?int $userId,
		?bool $isContentAlreadyLiked = null
	): array;

	public function likeContent(string $type, int $contentId, int $userId): array;
}
