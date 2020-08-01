<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Traits\Cdn;
use Breeze\Traits\ComponentsTrait;
use Breeze\Traits\RequestTrait;

abstract class BaseController implements ControllerInterface
{
	use RequestTrait;
	use Cdn;
	use ComponentsTrait;

	public function subActionCall(): void
	{
		$subActions = $this->getSubActions();
		$subAction = $this->getRequest('sa', $this->getMainAction());

		if (in_array($subAction, $subActions))
			$this->$subAction();

		else
			$this->{$this->getMainAction()}();
	}

	public abstract function getSubActions(): array;

	public abstract function getMainAction(): string;
}
