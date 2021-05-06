<?php

declare(strict_types=1);

namespace Breeze\Model;

interface UserModelInterface extends BaseModelInterface
{
	public const JSON_VALUES = ['cover', 'petitionList', 'moodHistory'];

	public const ARRAY_VALUES = ['blockListIDs'];

	public function loadMinData(array $userIds): array;

	public function getUserSettings(int $userId): array;

	public function wannaSeeBoards(): array;
}
