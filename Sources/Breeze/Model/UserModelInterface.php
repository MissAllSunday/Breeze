<?php

declare(strict_types=1);

namespace Breeze\Model;

interface UserModelInterface extends BaseModelInterface
{
	const JSON_VALUES = ['cover', 'petitionList', 'moodHistory'];
	const ARRAY_VALUES = ['blockListIDs'];

	public function loadMinData(array $userIds): array;

	public function updateProfileViews(array $data, int $userId): int;

	public function getUserSettings(int $userId): array;

	public function getViews($userId = 0): array;

	public function deleteViews($userId): void;

	public function wannaSeeBoards(): array;
}
