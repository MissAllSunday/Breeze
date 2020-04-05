<?php

declare(strict_types=1);


namespace Breeze\Repository\User;

use Breeze\Entity\MoodBaseEntity;
use Breeze\Model\MoodModelInterface;
use Breeze\Repository\BaseRepository;

class MoodRepository extends BaseRepository implements MoodRepositoryInterface
{
	/**
	 * @var MoodModelInterface
	 */
	private $moodModel;

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
		return $this->moodModel->getChunk($start, $maxIndex);
	}

	public function getActiveMoods(): array
	{
		return $this->moodModel->getMoodsByStatus(MoodBaseEntity::STATUS_ACTIVE);
	}

	public function getCount(): int
	{
		// TODO implement cache at repository level
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
