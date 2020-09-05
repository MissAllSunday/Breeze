<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\CommentEntity;
use Breeze\Model\CommentModelInterface;

class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
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
		$newCommentId = $this->commentModel->insert(array_merge($data, [
			CommentEntity::COLUMN_TIME => time(),
			CommentEntity::COLUMN_LIKES => 0,
		]));

		if (!$newCommentId)
			throw new InvalidCommentException('error_save_comment');

		return $newCommentId;
	}

	public function getByProfile(int $profileOwnerId = 0): array
	{
		$commentsByProfile = $this->getCache(__METHOD__ . $profileOwnerId);

		if (null === $commentsByProfile)
		{
			$commentsByProfile = $this->commentModel->getByProfiles([$profileOwnerId]);

			$this->setCache(__METHOD__ . $profileOwnerId, $commentsByProfile);
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

			$this->setCache(__FUNCTION__ . $commentId, $comment);
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

	/**
	 * @throws InvalidCommentException
	 */
	public function deleteByStatusId(int $statusId): bool
	{
		$wasDeleted = $this->commentModel->deleteByStatusId([$statusId]);

		if (!$wasDeleted)
			throw new InvalidCommentException('error_no_comment');

		return $wasDeleted;
	}
}
