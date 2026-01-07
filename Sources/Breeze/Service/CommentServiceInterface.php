<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\InvalidCommentException;
use Breeze\Util\Validate\DataNotFoundException;

interface CommentServiceInterface
{
	/**
	 * @throws InvalidCommentException
	 * @return array [CommentEntity]
	 */
	public function save(array $data): array;

	/**
	 * @throws DataNotFoundException
	 */
	public function deleteById(int $commentId): bool;
}

