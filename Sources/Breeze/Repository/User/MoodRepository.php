<?php

declare(strict_types=1);


namespace Breeze\Repository\User;

use Breeze\Entity\MoodEntity;
use Breeze\Exceptions\NoMoodFoundException;
use Breeze\Model\MoodModelInterface;
use Breeze\Repository\BaseRepository;

class MoodRepository extends BaseRepository implements MoodRepositoryInterface
{
	private const CACHE_ALL = '::getAllMoods';

	private const CACHE_ID = '::getById';

	public function __construct(private MoodModelInterface $moodModel)
	{
	}

	public function deleteByIds(array $toDeleteMoodIds): bool
	{
		$wasDeleted = $this->moodModel->delete($toDeleteMoodIds);

		if ($wasDeleted) {
			$this->setCache(self::class . self::CACHE_ALL, null);

			foreach ($toDeleteMoodIds as $moodId) {
				$this->setCache(self::class . self::CACHE_ID . $moodId, null);
			}
		}

		return $wasDeleted;
	}

	public function getChunk(int $start = 0, int $maxIndex = 0): array
	{
		// TODO implement cache at repository level
		return $this->moodModel->getChunk([
			'start' => $start,
			'maxIndex' => $maxIndex,
		]);
	}

	public function getActiveMoods(): array
	{
		return $this->moodModel->getMoodsByStatus(MoodEntity::STATUS_ACTIVE);
	}

	public function getAllMoods(): array
	{
		$allMoods = $this->getCache(__METHOD__);

		if (empty($allMoods)) {
			$allMoods = $this->moodModel->getAllMoods();
			$this->setCache(__METHOD__, $allMoods);
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

	public function updateMood(array $mood, int $moodId): array
	{
		return $this->moodModel->update($mood, $moodId);
	}

	/**
	 * @throws NoMoodFoundException
	 */
	public function insertMood(array $mood): array
	{
		$moodId =  $this->moodModel->insert($mood);

		return $this->getById($moodId);
	}

	/**
	 * @throws NoMoodFoundException
	 */
	public function getById(int $moodId): array
	{
		$mood = $this->getCache(__METHOD__ . $moodId);

		if (empty($mood)) {
			$mood = $this->moodModel->getByIds([$moodId]);

			$this->setCache(__METHOD__ . $moodId, $mood);
		}

		if (empty($mood)) {
			throw new NoMoodFoundException('error_no_mood');
		}

		return $mood;
	}

	public function getMoodProfile(int $userId, array $area): void
	{
		// TODO: Implement getMoodProfile() method.
	}
}
