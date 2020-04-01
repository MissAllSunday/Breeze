<?php

declare(strict_types=1);


namespace Breeze\Controller\User;

use Breeze\Breeze;
use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;

class WallController extends BaseController implements ControllerInterface
{
	public const SUB_ACTIONS = [
		'main',
		'status',
		'comment',
	];

	public function dispatch(): void
	{
		$this->service->initPage();
		$this->subActionCall();
	}

	public function main(): void
	{
		$this->service->isAllowedToSeePage(true);

		$this->render(__FUNCTION__);
	}

	public function render(string $subTemplate, array $params = []): void
	{
		$this->service->setSubActionContent($subTemplate);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
}
