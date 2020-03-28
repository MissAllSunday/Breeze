<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\StatusEntity;

class CommentRepository extends BaseRepository implements RepositoryInterface
{
	public function getCommentsByProfile(int $profileOwnerId = 0): void
	{
		$status = $this->model->getBy(CommentEntity::COLUMN_PROFILE_ID, [$profileOwnerId]);
	}
}
