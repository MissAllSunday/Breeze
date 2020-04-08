<?php

declare(strict_types=1);

namespace Breeze\Service\Actions;

use Breeze\Breeze;
use Breeze\Service\BaseService;

abstract class ActionsBaseService extends BaseService implements ActionsServiceInterface
{
	public function defaultSubActionContent(
		string $subActionName,
		array $templateParams = [],
		string $smfTemplate = ''
	): void
	{
		if (empty($subActionName))
			return;

		$context = $this->global('context');
		$scriptUrl = $this->global('scripturl');

		if (!isset($context[Breeze::NAME]))
			$context[Breeze::NAME] = [];

		if (!empty($templateParams))
			$context = array_merge($context, $templateParams);

		$context['page_title'] = $this->getText($this->getActionName() . '_' . $subActionName . '_title');

		$context['sub_template'] = !empty($smfTemplate) ?
			$smfTemplate : ($this->getActionName() . '_' . $subActionName);

		$context['linktree'][] = [
			'url' => $scriptUrl . '?action=' . $this->getActionName(),
			'name' => $context['page_title'],
		];

		$this->setGlobal('context', $context);
	}

	public abstract function init(array $subActions):void;

	public abstract function getActionName(): string;
}
