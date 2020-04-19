<?php

declare(strict_types=1);


namespace Breeze\Repository;

interface CommentRepositoryInterface
{
	public function getStatusByProfile(int $profileOwnerId = 0): array;
	public function getCommentsByStatus(array $statusIds = []): void;
}
