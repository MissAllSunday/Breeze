<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\MoodEntity as MoodEntity;

interface MoodModelInterface
{
	function insert(array $data, int $id = 0): int;

	function update(array $data, int $id = 0): array;

	public function getMoodByIDs(array $moodIds): array;

	public function getAllMoods(): array;

	public function getMoodsByStatus(int $status): array;
}
