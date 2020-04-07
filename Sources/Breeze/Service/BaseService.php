<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Traits\TextTrait;

abstract class BaseService
{
	use TextTrait;

	public function defaultSubActionContent(
		string $actionName,
		array $templateParams = [],
		string $smfTemplate = ''
	): void
	{
		if (empty($actionName))
			return;

		$context = $this->global('context');
		$scriptUrl = $this->global('scripturl');

		if (!isset($context[Breeze::NAME]))
			$context[Breeze::NAME] = [];

		if (!empty($templateParams))
			$context = array_merge($context, $templateParams);

		$context['page_title'] = $this->getText('page_' . $actionName . '_title');

		$context['sub_template'] = !empty($smfTemplate) ?
			$smfTemplate : ($this->getActionName() . '_' . $actionName);

		$context['linktree'][] = [
			'url' => $scriptUrl . '?action=' . $this->getActionName(),
			'name' => $context['page_title'],
		];

		$this->setGlobal('context', $context);
	}

	public function redirect(string $urlName): void
	{
		if(!empty($urlName))
			redirectexit($urlName);
	}

	protected abstract function getActionName(): string;
}
