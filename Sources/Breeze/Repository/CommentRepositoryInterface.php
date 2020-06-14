<?php

declare(strict_types=1);


namespace Breeze\Repository;

interface CommentRepositoryInterface
{
	/**
	 * @throws InvalidCommentException
	 */
	public function save(array $data): int;

	public function getByProfile(int $profileOwnerId = 0): array;

	public function getByStatus(array $statusIds = []): void;

	/**
	 * @throws InvalidCommentException
	 */
	public function getById(int $commentId): array;

	/**
	 * @throws InvalidCommentException
	 */
	public function deleteById(int $commentId): bool;

	public function cleanCache(string $cacheName): void;
}
