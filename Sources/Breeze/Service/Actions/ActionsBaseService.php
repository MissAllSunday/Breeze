<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

use Breeze\Breeze;
use Breeze\Service\BaseService;
use Breeze\Util\Permissions;

abstract class ActionsBaseService extends BaseService implements ActionsServiceInterface
{
	public function defaultSubActionContent(
		string $subActionName,
		array $templateParams = [],
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

	abstract public function init(array $subActions):void;

	abstract public function getActionName(): string;
}
