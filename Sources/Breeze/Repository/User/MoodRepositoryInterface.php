<?php

declare(strict_types=1);


namespace Breeze\Repository\User;


interface MoodRepositoryInterface
{
	public function deleteByIds(array $toDeleteMoodIds): bool;

	public function getChunk(int $start = 0, int $maxIndex = 0): array;

	public function getCount(): int;

	public function getActiveMoods(): array;

	public function saveMood($mood): bool;
}
