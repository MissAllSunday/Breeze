<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\LikeRepositoryInterface;

class LikeService extends BaseService implements LikeServiceInterface
{
	private LikeRepositoryInterface $likeRepository;

	public function __construct(
		LikeRepositoryInterface $likeRepository
	) {

		$this->likeRepository = $likeRepository;
	}

	public function getByContent(string $type, int $contentId): array
	{
		return $this->likeRepository->getByContent($type, $contentId);
	}

	public function isContentAlreadyLiked(string $type, int $contentId, int $userId): bool
	{
		return $this->likeRepository->isContentAlreadyLiked($type, $contentId, $userId);
	}
}
