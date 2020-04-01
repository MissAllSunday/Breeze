<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\StatusEntity;

class StatusRepository extends BaseRepository implements RepositoryInterface
{
	 public function getStatusByProfile(int $profileOwnerId = 0): void
	 {
		$statusIds = [];
		$statusUsersIds = [];
		$statusByProfile = $this->model->getChunk(0, 10, [
			'columnName' => StatusEntity::COLUMN_OWNER_ID,
			'ids' => [$profileOwnerId]
		]);

		foreach ($statusByProfile as $status)
		{
			$statusUsersIds[] = $status[StatusEntity::COLUMN_OWNER_ID];
			$statusUsersIds[] = $status[StatusEntity::COLUMN_POSTER_ID];

		}
	 }
}
