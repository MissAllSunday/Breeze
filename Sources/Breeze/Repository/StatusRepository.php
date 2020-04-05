<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\StatusEntity;
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
			'columnName' => StatusEntity::COLUMN_OWNER_ID,
			'ids' => [$profileOwnerId]
		]);

		foreach ($statusByProfile as $status)
		{
			$statusUsersIds[] = $status[StatusEntity::COLUMN_OWNER_ID];
			$statusUsersIds[] = $status[StatusEntity::COLUMN_POSTER_ID];

		}

		return $statusIds;
	 }

	public function getModel(): StatusModelInterface
	{
		return$this->statusModel;
	}
}
