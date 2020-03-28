<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Breeze;

class WallService extends BaseService implements ServiceInterface
{
	public const ACTION = 'breeze';

	public function setSubActionContent(
		string $actionName,
		array $templateParams = [],
		string $smfTemplate = ''
	): void
	{
		if (empty($actionName))
			return;

		loadCSSFile('breeze.css', [], 'smf_breeze');
		$context = $this->global('context');
		$scriptUrl = $this->global('scripturl');

		if (!isset($context[Breeze::NAME]))
			$context[Breeze::NAME] = [];

		if (!empty($templateParams))
			$context = array_merge($context, $templateParams);

		$context['page_title'] = $this->getText('general_wall');
		$context['sub_template'] = !empty($smfTemplate) ?
			$smfTemplate : (self::ACTION . '_' . $actionName);
		$context['linktree'][] = [
			'url' => $scriptUrl . '?action=' . self::ACTION,
			'name' => $context['page_title'],
		];

		$this->setGlobal('context', $context);
	}
}
