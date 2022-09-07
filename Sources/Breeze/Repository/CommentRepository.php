<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\CommentEntity;
use Breeze\Exceptions\InvalidCommentException;
use Breeze\Model\CommentModelInterface;

class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
	public function __construct(private CommentModelInterface $commentModel)
	{
	}

	/**
	 * @throws InvalidCommentException
	 */
	public function save(array $data): int
	{
		$newCommentId = $this->commentModel->insert(array_merge($data, [
			CommentEntity::CREATED_AT => time(),
			CommentEntity::LIKES => 0,
		]));

		if (!$newCommentId) {
			throw new InvalidCommentException('error_save_comment');
		}

		return $newCommentId;
	}

	public function getByProfile(int $profileOwnerId = 0): array
	{
		$commentsByProfile = $this->getCache(__METHOD__ . $profileOwnerId);

		if (empty($commentsByProfile)) {
			$commentsByProfile = $this->commentModel->getByProfiles([$profileOwnerId]);

			$this->setCache(__METHOD__ . $profileOwnerId, $commentsByProfile);
		}

		return $commentsByProfile;
	}

	public function getByStatus(array $statusIds = []): array
	{
		return $this->commentModel->getByStatus($statusIds);
	}

	/**
	 * @throws InvalidCommentException
	 */
	public function getById(int $commentId): array
	{
		$comment = $this->getCache(__FUNCTION__ . $commentId);

		if (empty($comment)) {
			$comment = $this->commentModel->getByIds([$commentId]);

			$this->setCache(__FUNCTION__ . $commentId, $comment);
		}

		if (empty($comment['data'])) {
			throw new InvalidCommentException('error_no_comment');
		}

		return $comment;
	}

	/**
	 * @throws InvalidCommentException
	 */
	public function deleteById(int $commentId): bool
	{
		$wasDeleted = $this->commentModel->delete([$commentId]);

		if (!$wasDeleted) {
			throw new InvalidCommentException('error_no_comment');
		}

		$this->setCache(self::class . '::getById' . $commentId, null);

		return true;
	}

	/**
	 * @throws InvalidCommentException
	 */
	public function deleteByStatusId(int $statusId): bool
	{
		if (!$this->commentModel->deleteByStatusId([$statusId])) {
			throw new InvalidCommentException('error_no_comment');
		}

		return true;
	}
}
