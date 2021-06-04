<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\LikeModelInterface;

class LikeRepository extends BaseRepository implements LikeRepositoryInterface
{
	private LikeModelInterface $likeModel;

	public function __construct(
		LikeModelInterface $likeModel
	) {
		$this->likeModel = $likeModel;
	}

	public function getByContent(string $type, int $contentId): array
	{
		return $this->likeModel->getByContent($type, $contentId);
	}

	public function isContentAlreadyLiked(string $type, int $contentId, int $userId): bool
	{
		return (bool) $this->likeModel->checkLike($type, $contentId, $userId);
	}
}
