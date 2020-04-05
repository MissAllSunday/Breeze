<?php

declare(strict_types=1);


namespace Breeze\Repository;

interface CommentRepositoryInterface
{
	public function getCommentsByProfile(int $profileOwnerId = 0): void;
}
