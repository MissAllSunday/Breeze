<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Exceptions\InvalidLikeException;
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

	/**
	 * @throws InvalidLikeException
	 */
	public function delete(string $type, int $contentId, int $userId): void
	{
		$wasDeleted = $this->likeModel->deleteContent($type, $contentId, $userId);

		if (!$wasDeleted) {
			throw new InvalidLikeException('error_no_like');
		}
	}

	/**
	 * @throws InvalidLikeException
	 */
	public function insert(string $type, int $contentId, int $userId): void
	{
		$this->likeModel->insertContent($type, $contentId, $userId);

		if (!$this->isContentAlreadyLiked($type, $contentId, $userId)) {
			throw new InvalidLikeException('error_save_like');
		}
	}

	public function count(string $type, int $contentId): int
	{
		return $this->likeModel->countContent($type, $contentId);
	}
}
