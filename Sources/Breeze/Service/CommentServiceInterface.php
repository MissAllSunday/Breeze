<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\InvalidCommentException;

interface CommentServiceInterface extends BaseServiceInterface
{
	public function saveAndGet(array $data): array;

	/**
	 * @throws InvalidCommentException
	 */
	public function deleteById(int $commentId): bool;

	/**
	 * @throws InvalidCommentException
	 */
	public function getById(int $commentId): array;

	public function getByProfile($profileOwnerId): array;

	public function getByStatusId(int $statusId): array;
}
