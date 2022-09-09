<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Util\ActionLogs\ActionLogInterface;

class ActivityRepository extends BaseRepository implements ActivityRepositoryInterface
{
	public function insertLog(string $actionName, array $actionData): void
	{
	}

	public function getById(int $actionId): ?ActionLogInterface
	{
		return null;
	}
}
