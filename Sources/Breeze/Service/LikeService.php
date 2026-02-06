<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Repository\LikeRepositoryInterface;

class LikeService implements LikeServiceInterface
{
	public function __construct(protected LikeRepositoryInterface $likeRepository)
	{
	}

	public function countOrphans(): int
	{
		return $this->likeRepository->countOrphans();
	}

	public function deleteOrphans(): void
	{
		$this->likeRepository->deleteOrphans();
	}
}
