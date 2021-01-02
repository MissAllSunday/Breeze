<?php

declare(strict_types=1);


namespace Breeze\Service;

interface MoodServiceInterface extends BaseServiceInterface
{
	public const DISPLAY_PROFILE_AREAS = ['summary', 'static'];

	public function getAll(): array;

	public function getActiveMoods(): array;

	public function getPlacementField(): int;

	public function displayMood(array &$data, int $userId): void;

	public function moodProfile(int $memID, array $area): void;

	public function deleteMoods(array $toDeleteMoodIds): bool;

	public function getMoodById(int $moodId): array;

	public function saveMood(array $mood, int $moodId): bool;

	public function showMoodOnCustomFields(int $userId): void;
}
