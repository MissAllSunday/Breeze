<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Traits\RequestTrait;
use Breeze\Traits\TextTrait;

abstract class BaseController implements ControllerInterface
{
	use RequestTrait;
	use TextTrait;

	public function subActionCall(): void
	{
		$subActions = $this->getSubActions();
		$subAction = $this->getRequest('sa', $this->getMainAction());

		if (in_array($subAction, $subActions))
			$this->$subAction();

		else
			$this->{$this->getMainAction()}();
	}

	public function error(string $errorTextKey, string $templateName = ''): void
	{
		$this->render(!empty($templateName) ? $templateName : __FUNCTION__, [
			'errorMessage' => $this->getText($errorTextKey)
		]);
	}

	public abstract function getSubActions(): array;

	public abstract function getMainAction(): string;
}
