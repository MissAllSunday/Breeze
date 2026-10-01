<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\InvalidCommentException;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;

interface CommentServiceInterface
{
	/**
	 * The author is always the session user; any `user_id` in $data is ignored.
	 *
	 * @throws InvalidCommentException
	 * @return array [CommentEntity]
	 */
	public function save(array $data): array;

	/**
	 * @throws DataNotFoundException when the comment or its status is missing
	 * @throws NotAllowedException when the session user may not delete it
	 */
	public function deleteById(int $commentId): bool;

	public function countOrphans(): int;

	public function deleteOrphans(): void;

	public function recountLikes(): void;
}
