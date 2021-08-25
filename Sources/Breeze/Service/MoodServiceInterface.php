<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\InvalidMoodException;

interface MoodServiceInterface extends BaseServiceInterface
{
	public const DISPLAY_PROFILE_AREAS = ['summary', 'static'];

	public function moodList(): array;

	public function getAll(): array;

	public function getActiveMoods(): array;

	public function getPlacementField(): int;

	public function displayMood(array &$data, int $userId): void;

	public function moodProfile(int $memID, array $area): void;

	public function deleteMoods(array $toDeleteMoodIds): bool;

	/**
	 * @throws InvalidMoodException
	 */
	public function getMoodById(int $moodId): array;

	public function saveMood(array $mood): array;
}
