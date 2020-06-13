<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\CommentModelInterface;

class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
	/**
	 * @var CommentModelInterface
	 */
	private $commentModel;

	public function __construct(CommentModelInterface $commentModel)
	{
		$this->commentModel = $commentModel;
	}

	public function save(array $data): int
	{
		return $this->commentModel->insert($data);
	}

	public function getByProfile(int $profileOwnerId = 0): array
	{
		$commentsByProfile = $this->getCache($this->cacheKey(__METHOD__));

		if (null === $commentsByProfile)
		{
			$commentsByProfile = $this->commentModel->getByProfiles([$profileOwnerId]);

			$this->setCache($this->cacheKey(__METHOD__), $commentsByProfile);
		}

		return $commentsByProfile;
	}

	public function getModel(): CommentModelInterface
	{
		return $this->commentModel;
	}

	public function getByStatus(array $statusIds = []): void
	{
		// TODO: Implement getCommentsByStatus() method.
	}

	public function getById(int $commentId): array
	{
		$comment = $this->getCache(__METHOD__ . $commentId);

		if (null === $comment)
		{
			$comment = $this->commentModel->getByIds([$commentId]);

			$this->setCache($this->cacheKey(__METHOD__ . $commentId), $comment);
		}

		return $comment;
	}

	public function deleteById(int $commentId): void
	{
		$this->commentModel->delete([$commentId]);
	}

	public function cleanCache(string $cacheName): void
	{
		$this->setCache($cacheName, null);
	}
}
