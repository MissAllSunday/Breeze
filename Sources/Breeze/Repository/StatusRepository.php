<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\StatusEntity;
use Breeze\Model\StatusModel;

class StatusRepository extends BaseRepository implements RepositoryInterface
{
	 public function getStatusByProfile($profileOwnerId = 0): void
	 {
		$status = $this->model->getBy(StatusEntity::COLUMN_OWNER_ID, [$profileOwnerId]);
	 }
}
