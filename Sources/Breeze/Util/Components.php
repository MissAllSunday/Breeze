<?php

declare(strict_types=1);

namespace Breeze\Util;

use Breeze\Breeze;
use Breeze\Traits\TextTrait;

class Components
{
	use TextTrait;

	public const TABS_FILE = 'tabs.js';
	public const CSS_FILE = 'breeze.css';
	public const FOLDER = 'breezeComponents/';
	private const COMPONENTS = [];
	private const CDN_JS = [
		'react' => 'https://unpkg.com/react@' . Breeze::REACT_VERSION . '/umd/react.production.min.js',
		'reactDom' => 'https://unpkg.com/react-dom@' . Breeze::REACT_DOM_VERSION . '/umd/react-dom.production.min.js',
	];

	public function loadUIVars(array $vars = []): void
	{
		foreach ($vars as $varName => $varValue) {
			$this->addJavaScriptVar(
				$varName,
				$varValue
			);
		}
	}

	public function loadComponents(array $components = []): void
	{
		$componentsToLoad = array_intersect(self::COMPONENTS, $components);

		$this->loadJsDependencies();

		foreach ($componentsToLoad as $component) {
			$this->loadJavaScriptFile(self::FOLDER . $component . '.js', [
				'defer' => false,
				'default_theme' => true,
			], strtolower(Breeze::PATTERN . $component));
		}
	}

	public function loadJavaScriptFile(string $fileName, array $params = [], string $nameIdentifier = ''): void
	{
		loadJavaScriptFile($fileName, $params, $nameIdentifier);
	}

	public function loadCSSFile(string $fileName, array $params = [], $nameIdentifier = ''): void
	{
		loadCSSFile($fileName, $params, $nameIdentifier);
	}

	public function addJavaScriptVar(string $variable, $value): void
	{
		addJavaScriptVar(
			strtolower(Breeze::NAME) . ucfirst($variable),
			Json::encode($value)
		);
	}

	public function loadTxtVarsFor(array $components): void
	{
		$this->setLanguage(Breeze::NAME);

		$components = array_merge(['general'], $components);

		$componentVariables = [
			'error' => [
				'wrongValues' => $this->getText('error_wrong_values'),
				'errorEmpty' => $this->getText('error_empty'),
				'noStatus' => $this->getText('page_no_status'),
			],
			'general' => [
				'save' => $this->getText('general_save'),
				'delete' => $this->getText('general_delete'),
				'editing' => $this->getText('general_editing'),
				'close' => $this->getText('general_close'),
				'cancel' => $this->getText('general_cancel'),
				'send' => $this->getText('general_send'),
				'preview' => $this->getText('general_preview'),
				'previewing' => $this->getText('general_previewing'),
				'end' => $this->getText('info_loading_end'),
				'loadMore' => $this->getText('load_more'),
			],
			'tabs' => [
				'wall' => $this->getText('tabs_wall'),
				'about' => $this->getText('tabs_about'),
				'activity' => $this->getText('tabs_activity'),
			],
			'like' => [
				'like' => $this->getSmfText('like'),
				'unlike' => $this->getSmfText('unlike'),
			],
		];

		foreach ($components as $name) {
			if (empty($componentVariables[$name])) {
				continue;
			}

			$this->addJavaScriptVar(
				'Txt' . ucfirst($name),
				$componentVariables[$name]
			);
		}
	}

	protected function loadJsDependencies(): void
	{
		foreach (self::CDN_JS as $jsDependency) {
			$this->loadJavaScriptFile($jsDependency, [
				'external' => true,
				'defer' => false,
			], strtolower(Breeze::PATTERN . $jsDependency));
		}
	}
}
