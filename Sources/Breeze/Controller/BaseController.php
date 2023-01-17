<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Breeze;
use Breeze\Traits\PersistenceTrait;
use Breeze\Traits\RequestTrait;
use Breeze\Traits\TextTrait;
use Breeze\Util\Permissions;

abstract class BaseController implements ControllerInterface
{
	use RequestTrait;
	use TextTrait;
	use PersistenceTrait;

	public function defaultSubActionContent(
		string $subActionName,
		array  $templateParams = [],
		string $smfTemplate = ''
	): void {
		if (empty($subActionName)) {
			return;
		}

		$context = $this->global('context');
		$scriptUrl = $this->global('scripturl');

		if (!isset($context[Breeze::NAME])) {
			$context[Breeze::NAME] = [];
		}

		if (!empty($templateParams)) {
			$context = array_merge($context, $templateParams);
		}

		$context['page_title'] = $this->getText($this->getActionName() . '_' . $subActionName . '_title');

		$context['sub_template'] = !empty($smfTemplate) ?
			$smfTemplate : ($this->getActionName() . '_' . $subActionName);

		$context['linktree'][] = [
			'url' => $scriptUrl . '?action=' . $this->getActionName(),
			'name' => $context['page_title'],
		];
		$context['can_send_pm'] = allowedTo('pm_send');
		$context[Permissions::USE_MOOD] = Permissions::isAllowedTo(Permissions::USE_MOOD);

		$this->setGlobal('context', $context);
	}

	public function subActionCall(): void
	{
		$subActions = $this->getSubActions();
		$subAction = $this->getRequest('sa', $this->getMainAction());

		if (in_array($subAction, $subActions)) {
			$this->$subAction();
		} else {
			$this->{$this->getMainAction()}();
		}
	}

	public function error(string $errorTextKey, string $templateName = ''): void
	{
		$this->render(!empty($templateName) ? $templateName : __FUNCTION__, [
			'errorMessage' => $this->getText($errorTextKey),
		]);
	}

	abstract public function getSubActions(): array;

	abstract public function getMainAction(): string;

	abstract public function getActionName(): string;
}
