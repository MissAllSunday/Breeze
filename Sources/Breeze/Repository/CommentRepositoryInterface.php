<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\CommentHandledEntity;

interface CommentRepositoryInterface extends BaseRepositoryInterface
{
	/**
	 * @throws InvalidCommentException
	 */
	public function insert(CommentEntity $commentEntity): CommentHandledEntity;

	public function getByProfile(array $userProfiles = []): array;

	public function getByStatus(array $statusIds = []): array;

	public function getById(int $id): array;

	public function deleteById(int $commentId): bool;

	public function deleteByStatusId(int $statusId): bool;
}
