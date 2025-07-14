<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\CommentHandledEntity;

interface CommentRepositoryInterface extends BaseRepositoryInterface
{
	/**
	 * @throws InvalidCommentException
	 * @return array [CommentHandledEntity]
	 */
	public function insert(CommentEntity $commentEntity): array;

	public function getByProfile(array $userProfiles = []): array;

	public function getByStatus(array $statusIds = []): array;

	public function deleteById(int $commentId): bool;

	public function deleteByStatusId(int $statusId): bool;

	public function getById(int $id = 0): CommentEntity;
}
