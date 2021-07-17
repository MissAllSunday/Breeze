<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Util\ActionLogs\ActionLogInterface;

interface ActivityServiceInterface
{
	public function setLog(string $actionName, array $actionData): bool;

	public function getLog(string $actionName): ?ActionLogInterface;
}
