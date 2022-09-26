<?php

declare(strict_types=1);


namespace Breeze\Repository\User;

use Breeze\Repository\InvalidMoodException;

interface MoodRepositoryInterface
{
	public function deleteByIds(array $toDeleteMoodIds): bool;

	public function getChunk(int $start = 0, int $maxIndex = 0): array;

	public function getCount(): int;

	public function getAllCount(): int;

	public function getAllMoods(): array;

	public function getActiveMoods(): array;

	/**
	 * @throws InvalidMoodException
	 */
	public function getById(int $moodId): array;

	public function getMoodProfile(int $userId, array $area);

	public function updateMood(array $mood, int $moodId): array;

	public function insertMood(array $mood): array;
}
