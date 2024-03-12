<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Traits\PersistenceTrait;
use Breeze\Traits\RequestTrait;
use Breeze\Traits\TextTrait;
use Breeze\Util\Error;
use Breeze\Util\Permissions;

abstract class BaseController implements ControllerInterface
{
	use RequestTrait;
	use TextTrait;
	use PersistenceTrait;

	public function dispatch(): void
	{
		Permissions::isNotGuest($this->getText('error_no_access'));

		if (!$this->isEnable(SettingsEntity::MASTER)) {
			Error::show('no_valid_action');
		}

		$this->setLanguage(Breeze::NAME);
		$this->setTemplate(Breeze::NAME);

		$this->subActionCall();
	}

	public function render(
		string $subActionName,
		array  $templateParams = [],
		string $smfTemplate = ''
	): void {
		if (empty($subActionName)) {
			return;
		}

		$context = $this->global('context');

		if (!isset($context[Breeze::NAME])) {
			$context[Breeze::NAME] = [];
		}

		if (!empty($templateParams)) {
			$context = array_merge($context, $templateParams);
		}

		$context['sub_template'] = $subActionName;

		$this->setGlobal('context', $context);
	}

	public function subActionCall(): void
	{
		$subActions = $this->getSubActions();
		$subAction = $this->getRequest($this->getActionVarName(), $this->getMainAction());

		if ($subAction === 'editor') {
			var_dump($subAction);var_dump($subActions);die;
		}

		if (in_array($subAction, $subActions)) {
			$this->{$subAction}();
		} else {
			Error::show('no_valid_action');
		}
	}

	public function error(string $errorTextKey, string $templateName = ''): void
	{
		$this->render(!empty($templateName) ? $templateName : __FUNCTION__, [
			'errorMessage' => $this->getText($errorTextKey),
		]);
	}

	public function getActionVarName():string
	{
		return 'sa';
	}

	abstract public function getSubActions(): array;

	abstract public function getMainAction(): string;

	abstract public function getActionName(): string;
}
