<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

use Breeze\Service\BaseServiceInterface;

interface ActionsServiceInterface extends BaseServiceInterface
{
	public function init(array $subActions): void;

	public function defaultSubActionContent(string $subActionName, array $templateParams, string $smfTemplate);

	public function getActionName(): string;
}
