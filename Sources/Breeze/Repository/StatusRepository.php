<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\StatusEntity;
use Breeze\Exceptions\InvalidCommentException;
use Breeze\Exceptions\InvalidStatusException;
use Breeze\Model\StatusModelInterface;

class StatusRepository extends BaseRepository implements StatusRepositoryInterface
{
	public function __construct(
		private StatusModelInterface $statusModel,
		private CommentRepositoryInterface $commentRepository,
		private LikeRepositoryInterface $likeRepository
	) {
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function save(array $data): int
	{
		$newStatusId = $this->statusModel->insert($data);

		if ($newStatusId === 0) {
			throw new InvalidStatusException('error_save_status');
		}

		return $newStatusId;
	}

	public function getByProfile(int $profileOwnerId = 0, int $start = 0): array
	{
		$status = $this->getCache(__METHOD__ . $profileOwnerId);

		if (empty($status)) {
			$status = $this->statusModel->getStatusByProfile([
				'start' => $start,
				'maxIndex' => $this->statusModel->getCount(),
				'ids' => [$profileOwnerId],
			]);

			if (!empty(array_filter($status))) {
				$this->setCache(__METHOD__ . $profileOwnerId, $status);
			}
		}

		$comments =  $this->commentRepository->getByProfile($profileOwnerId);
		$userIds = array_unique(array_merge($status['usersIds'], $comments['usersIds']));
		$status['data'] = $this->likeRepository->appendLikeData($status['data'], StatusEntity::ID);

		foreach ($status['data'] as $statusId => &$singleStatus) {
			$singleStatus['comments'] = $comments['data'][$statusId] ?? [];
		}

		return [
			'users' => $this->loadUsersInfo($userIds),
			'status' => $status['data'],
		];
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function getById(int $statusId = 0): array
	{
		$status = $this->statusModel->getById($statusId);

		if (!$status) {
			throw new InvalidStatusException('error_no_status');
		}

		return $status;
	}

	/**
	 * @throws InvalidStatusException
	 * @throws InvalidCommentException
	 */
	public function deleteById(int $statusId): bool
	{
		$this->commentRepository->deleteByStatusId($statusId);

		if (!$this->statusModel->delete([$statusId])) {
			throw new InvalidStatusException('error_no_comment');
		}

		$this->setCache(self::class . '::getById' . $statusId, null);

		return true;
	}
}
