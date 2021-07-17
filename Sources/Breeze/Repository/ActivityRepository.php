<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Model\ActivityModelInterface;
use Breeze\Util\ActionLogs\ActionLogInterface;

class ActivityRepository extends BaseRepository implements ActivityRepositoryInterface
{
	private ActivityModelInterface $activityModel;

	public function __construct(ActivityModelInterface $activityModel)
	{

		$this->activityModel = $activityModel;
	}

	public function insertLog(string $actionName, array $actionData): void
	{
	}

	public function getById(int $actionId): ?ActionLogInterface
	{
		return null;
	}
}
