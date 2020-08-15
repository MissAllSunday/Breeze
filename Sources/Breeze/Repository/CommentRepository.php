<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\CommentModelInterface;

class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
	/**
	 */
	private CommentModelInterface $commentModel;

	public function __construct(CommentModelInterface $commentModel)
	{
		$this->commentModel = $commentModel;
	}

	/**
	 * @throws InvalidCommentException
	 */
	public function save(array $data): int
	{
		$wasSaved = $this->commentModel->insert($data);

		if (!$wasSaved)
			throw new InvalidCommentException('error_save_comment');

		return $wasSaved;
	}

	public function getByProfile(int $profileOwnerId = 0): array
	{
		$commentsByProfile = $this->getCache($this->cacheKey(__FUNCTION__));

		if (null === $commentsByProfile)
		{
			$commentsByProfile = $this->commentModel->getByProfiles([$profileOwnerId]);

			$this->setCache($this->cacheKey(__FUNCTION__), $commentsByProfile);
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

	/**
	 * @throws InvalidCommentException
	 */
	public function getById(int $commentId): array
	{
		$comment = $this->getCache(__FUNCTION__ . $commentId);

		if (null === $comment)
		{
			$comment = $this->commentModel->getByIds([$commentId]);

			$this->setCache($this->cacheKey(__FUNCTION__ . $commentId), $comment);
		}

		if (empty($comment))
			throw new InvalidCommentException('error_no_comment');

		return $comment;
	}

	/**
	 * @throws InvalidCommentException
	 */
	public function deleteById(int $commentId): bool
	{
		$wasDeleted = $this->commentModel->delete([$commentId]);

		if (!$wasDeleted)
			throw new InvalidCommentException('error_no_comment');

		$this->cleanCache('getById' . $commentId);

		return $wasDeleted;
	}

	public function cleanCache(string $cacheName): void
	{
		$this->setCache($cacheName, null);
	}
}
