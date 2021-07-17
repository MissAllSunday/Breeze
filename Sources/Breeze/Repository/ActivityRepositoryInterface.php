<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Util\ActionLogs\ActionLogInterface;

interface ActivityRepositoryInterface
{
	public function insertLog(string $actionName, array $actionData);

	public function getById(int $actionId): ?ActionLogInterface;
}
