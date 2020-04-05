<?php

declare(strict_types=1);

namespace Breeze\Model;

interface MoodModelInterface extends BaseModelInterface
{
	public function getMoodByIDs(array $moodIds): array;

	public function getAllMoods(): array;

	public function getMoodsByStatus(int $status): array;
}
