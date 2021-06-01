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

	/**
	 * @throws InvalidLikeException
	 */
	public function getByContent(string $type, int $contentId): array
	{
		$like = $this->likeModel->getByContent($type, $contentId);

		if (!$like) {
			throw new InvalidLikeException('error_no_like');
		}

		return $like;
	}
}
