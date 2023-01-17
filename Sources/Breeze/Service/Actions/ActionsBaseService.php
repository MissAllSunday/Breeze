<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

use Breeze\Service\BaseService;

abstract class ActionsBaseService extends BaseService implements ActionsServiceInterface
{
	abstract public function init(array $subActions): void;

	abstract public function getActionName(): string;
}
