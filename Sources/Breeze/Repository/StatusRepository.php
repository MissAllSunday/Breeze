<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\StatusBaseEntity;
use Breeze\Model\StatusModelInterface;

class StatusRepository extends BaseRepository implements StatusRepositoryInterface
{
	/**
	 * @var StatusModelInterface
	 */
	private $statusModel;

	public function __construct(StatusModelInterface $statusModel)
	{
		$this->statusModel = $statusModel;
	}

	public function getStatusByProfile(int $profileOwnerId = 0): array
	 {
		$statusIds = [];
		$statusUsersIds = [];
		$statusByProfile = $this->statusModel->getChunk(0, 10, [
			'columnName' => StatusBaseEntity::COLUMN_OWNER_ID,
			'ids' => [$profileOwnerId]
		]);

		foreach ($statusByProfile as $status)
		{
			$statusUsersIds[] = $status[StatusBaseEntity::COLUMN_OWNER_ID];
			$statusUsersIds[] = $status[StatusBaseEntity::COLUMN_POSTER_ID];

		}

		return $statusIds;
	 }

	public function getModel(): StatusModelInterface
	{
		return$this->statusModel;
	}
}
