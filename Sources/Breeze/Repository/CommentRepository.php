<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\CommentEntity;
use Breeze\Model\CommentModelInterface;

class CommentRepository extends BaseRepository implements CommentRepositoryInterface
{
	public function __construct(
		private readonly CommentModelInterface   $commentModel,
		private readonly LikeRepositoryInterface $likeRepository
	) {
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

		if ($newCommentId === 0) {
			throw new InvalidCommentException('error_save_comment');
		}

		return $newCommentId;
	}

	public function getByProfile(array $userProfiles = []): array
	{
		$comments = $this->commentModel->getByProfiles($userProfiles);

		foreach ($comments['data'] as &$commentsByStatus) {
			$commentsByStatus = $this->appendLikes($commentsByStatus);
			$commentsByStatus = $this->appendUserData($commentsByStatus, $comments['usersIds']);
		}

		return $comments['data'];
	}

	public function getByStatus(array $statusIds = []): array
	{
		return $this->commentModel->getByStatus($statusIds);
	}

	/**
	 * @throws InvalidCommentException
	 */
	public function getById(int $id): array
	{
		$comments = $this->commentModel->getByIds([$id]);

		if (empty($comments['data'])) {
			throw new InvalidCommentException('error_no_comment');
		}

		return $this->prepareData($comments);
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

	protected function appendLikes(array $data = []): array
	{
		return $this->likeRepository->appendLikeData($data, CommentEntity::ID);
	}

	protected function appendUserData(array $data, array $userIds = []): array
	{
		$usersData = $this->loadUsersInfo(array_unique($userIds));

		return array_map(function (array $item) use ($usersData): array {
			$item['userData'] = $usersData[$item[CommentEntity::USER_ID]];

			return $item;
		}, $data);
	}

	protected function prepareData(array $comments = []): array
	{
		$comments['data'] = $this->appendUserData($comments['data'], array_unique($comments['usersIds']));
		$comments['data'] = $this->appendLikes($comments['data']);

		return $comments['data'];
	}
}
