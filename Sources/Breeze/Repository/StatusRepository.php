<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\StatusEntity;
use Breeze\Model\StatusModelInterface;
use Breeze\Util\Validate\DataNotFoundException;

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
		$newStatusId = $this->statusModel->insert(array_merge($data, [
			StatusEntity::CREATED_AT => time(),
			StatusEntity::LIKES => 0,
		]));

		if ($newStatusId === 0) {
			throw new InvalidStatusException('error_save_status');
		}

		return $newStatusId;
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function getByProfile(int $profileOwnerId = 0, int $start = 0): array
	{
		$status = $this->getCache(__METHOD__ . $profileOwnerId);

		if (empty($status)) {
			$status = $this->statusModel->getStatusByProfile([
				'start' => $start,
				'maxIndex' => $this->statusModel->getCount(),
				'ids' => [$profileOwnerId],
			]);

			if (!empty(array_filter($status['data']))) {
				$this->setCache(__METHOD__ . $profileOwnerId, $status);
			}
		}

		if (empty($status['data'])) {
			throw new DataNotFoundException('no_status');
		}

		$comments =  $this->commentRepository->getByProfile($profileOwnerId);
		$status['data'] = $this->likeRepository->appendLikeData($status['data'], StatusEntity::ID);

		$usersData = $this->loadUsersInfo(array_unique($status['usersIds']));

		foreach ($status['data'] as $statusId => $singleStatus) {
			$status['data'][$statusId]['userData'] = $usersData[$singleStatus[StatusEntity::USER_ID]];
			$status['data'][$statusId]['comments'] = $comments[$statusId] ?? [];
		}

		return $status['data'];
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function getById(int $statusId = 0): array
	{
		$status = $this->statusModel->getById($statusId);

		if (empty($status['data'])) {
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
		$this->getById($statusId);

		$this->commentRepository->deleteByStatusId($statusId);

		if (!$this->statusModel->delete([$statusId])) {
			throw new InvalidStatusException('error_no_status');
		}

		$this->setCache(self::class . '::getById' . $statusId, null);

		return true;
	}
}
