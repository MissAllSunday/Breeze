<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\StatusModelInterface;

class StatusRepository extends BaseRepository implements StatusRepositoryInterface
{
	private StatusModelInterface $statusModel;

	/**
	 * @throws InvalidStatusException
	 */
	public function save(array $data): int
	{
		$newStatusId = $this->statusModel->insert($data);

		if (!$newStatusId)
			throw new InvalidStatusException('error_save_status');

		return $newStatusId;
	}

	public function __construct(StatusModelInterface $statusModel)
	{
		$this->statusModel = $statusModel;
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function getByProfile(int $profileOwnerId = 0, int $start = 0): array
	 {
		$statusByProfile = $this->getCache($this->cacheKey(__METHOD__ . $profileOwnerId));

		if (null === $statusByProfile)
		{
			$statusByProfile = $this->statusModel->getStatusByProfile([
				'start' => $start,
				'maxIndex' => $this->statusModel->getCount(),
				'ids' => [$profileOwnerId]
			]);

			if (empty(array_filter($statusByProfile)))
				throw new InvalidStatusException('error_wall_none');

			$this->setCache($this->cacheKey(__METHOD__ . $profileOwnerId), $statusByProfile);
		}

		return $statusByProfile;
	 }

	/**
	 * @throws InvalidStatusException
	 */
	public function getById(int $statusId = 0): array
	{
		$status = $this->statusModel->getById($statusId);

		if (!$status)
			throw new InvalidStatusException('error_no_status');

		return $status;
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function deleteById(int $statusId): bool
	{
		$wasDeleted = $this->statusModel->delete([$statusId]);

		if (!$wasDeleted)
			throw new InvalidStatusException('error_no_comment');

		$this->cleanCache('getById' . $statusId);

		return $wasDeleted;
	}
}
