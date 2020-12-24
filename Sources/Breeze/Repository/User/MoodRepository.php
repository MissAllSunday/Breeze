<?php

declare(strict_types=1);


namespace Breeze\Repository\User;

use Breeze\Entity\MoodEntity;
use Breeze\Model\MoodModelInterface;
use Breeze\Repository\BaseRepository;

class MoodRepository extends BaseRepository implements MoodRepositoryInterface
{
	private MoodModelInterface $moodModel;

	public function __construct(MoodModelInterface $moodModel)
	{
		$this->moodModel = $moodModel;
	}

	public function deleteByIds(array $toDeleteMoodIds): bool
	{
		return $this->moodModel->delete($toDeleteMoodIds);
	}

	public function getChunk(int $start = 0, int $maxIndex = 0): array
	{
		// TODO implement cache at repository level
		return $this->moodModel->getChunk([
			'start' => $start,
			'maxIndex' => $maxIndex
		]);
	}

	public function getActiveMoods(): array
	{
		return $this->moodModel->getMoodsByStatus(MoodEntity::STATUS_ACTIVE);
	}

	public function getAllMoods(): array
	{
		$allMoods = $this->getCache(__FUNCTION__);

		if (empty($allMoods)) {
			$allMoods = $this->moodModel->getAllMoods();
			$this->setCache(__FUNCTION__, $allMoods);
		}

		return $allMoods;
	}

	public function getAllCount(): int
	{
		return count($this->getAllMoods());
	}

	public function getCount(): int
	{
		return $this->moodModel->getCount();
	}

	public function saveMood($mood): bool
	{
		return true;
	}

	public function getMoodProfile(int $userId, array $area): void
	{
		// TODO: Implement getMoodProfile() method.
	}

	public function getModel(): MoodModelInterface
	{
		return $this->moodModel;
	}
}
