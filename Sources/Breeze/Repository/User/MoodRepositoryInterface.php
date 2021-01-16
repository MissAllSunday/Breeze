<?php

declare(strict_types=1);


namespace Breeze\Repository\User;

use Breeze\Model\MoodModelInterface;

interface MoodRepositoryInterface
{
	public function deleteByIds(array $toDeleteMoodIds): bool;

	public function getChunk(int $start = 0, int $maxIndex = 0): array;

	public function getCount(): int;

	public function getAllCount(): int;

	public function getAllMoods(): array;

	public function getActiveMoods(): array;

	public function saveMood($mood): bool;

	public function getById(int $moodId): array;

	public function getMoodProfile(int $userId, array $area);

	public function getModel(): MoodModelInterface;
}
