<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\ActivityRepositoryInterface;
use Breeze\Util\ActionLogs\ActionLogInterface;

class ActivityService implements ActivityServiceInterface
{
	public function __construct(private ActivityRepositoryInterface $activityRepository)
	{
	}

	public function setLog(string $actionName, array $actionData): bool
	{
		$this->activityRepository->insertLog($actionName, $actionData);

		return true;
	}

	public function getLog(string $actionName): ?ActionLogInterface
	{
		return null;
	}
}
