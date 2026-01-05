<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\InvalidCommentException;

interface CommentServiceInterface
{
	/**
	 * @throws InvalidCommentException
	 * @return array [CommentEntity]
	 */
	public function save(array $data): array;

	/**
	 * @throws \Breeze\Util\Validate\DataNotFoundException
	 */
	public function deleteById(int $commentId): bool;
}

