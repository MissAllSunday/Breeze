<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\CommentEntity;

interface CommentRepositoryInterface extends BaseRepositoryInterface
{
	/**
	 * @throws InvalidCommentException
	 * @return array [CommentEntity]
	 */
	public function insert(CommentEntity $commentEntity): array;

	public function getByProfile(array $userProfiles = []): array;

	public function getByStatus(array $statusIds = []): array;

	public function deleteById(int $commentId): bool;

	public function deleteByStatusId(int $statusId): bool;

	public function getById(int $id = 0): CommentEntity;

	public function countOrphans(): int;

	public function deleteOrphans(): void;

	public function recountLikes(): void;
}
