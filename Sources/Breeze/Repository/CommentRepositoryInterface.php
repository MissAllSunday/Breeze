<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\BaseEntityInterface;

interface CommentRepositoryInterface extends BaseRepositoryInterface
{
	/**
	 * @throws InvalidCommentException
	 */
	public function save(array $data): int;

	public function getByProfile(array $userProfiles = []): array;

	public function getByStatus(array $statusIds = []): array;

	/**
	 * @throws InvalidCommentException
	 */
	public function getById(int $id): BaseEntityInterface;

	/**
	 * @throws InvalidCommentException
	 */
	public function deleteById(int $commentId): bool;

	/**
	 * @throws InvalidCommentException
	 */
	public function deleteByStatusId(int $statusId): bool;
}
