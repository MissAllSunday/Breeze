<?php

declare(strict_types=1);


namespace Breeze\Repository\User;

use Breeze\Repository\BaseRepository;
use Breeze\Repository\RepositoryInterface;

class MoodRepository extends BaseRepository implements RepositoryInterface
{
	public function deleteByIds(array $toDeleteMoodIds): bool
	{
		return $this->model->delete($toDeleteMoodIds);
	}

	public function getChunk(int $start = 0, int $maxIndex = 0): array
	{
		// TODO implement cache at repository level
		return $this->model->getChunk($start, $maxIndex);
	}

	public function getCount(): int
	{
		// TODO implement cache at repository level
		return $this->model->getCount();
	}
}
