<?php

declare(strict_types=1);

namespace Breeze\Util;

use Breeze\Breeze;
use Breeze\Traits\TextTrait;

class Components
{
	use TextTrait;

	public const string CSS_FILE = 'breeze.css';
	public const string FOLDER = 'breezeComponents/';

	public const string MAIN_JS_FILE = Components::FOLDER . Breeze::REACT_HASH . '.js';
	private const array COMPONENTS = [];

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
		// React and ReactDOM are bundled in the main JS file by Vite, no need to load from CDN
		$this->loadJavaScriptFile(Components::MAIN_JS_FILE, [
			'external' => false,
			'defer' => true,
		], strtolower(Breeze::PATTERN . Breeze::REACT_HASH));

		$this->loadCSSFile(Components::CSS_FILE, [], 'smf_breeze');
		$componentsToLoad = array_intersect(self::COMPONENTS, $components);

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
				'generic' => $this->getText('error_generic'),
			],
			'general' => [
				'deletedStatus' => $this->getText('success_deleted_status'),
				'deletedComment' => $this->getText('success_deleted_comment'),
				'save' => $this->getText('general_save'),
				'delete' => $this->getText('general_delete'),
				'close' => $this->getText('general_close'),
				'cancel' => $this->getText('general_cancel'),
				'send' => $this->getText('general_send'),
				'end' => $this->getText('info_loading_end'),
				'loadMore' => $this->getText('load_more'),
				'goUp' => $this->getSmfText('go_up'),
				'goBack' => $this->getText('general_goBack'),
				'emptyData' => $this->getText('info_empty_data'),
				'buddyAdd' => $this->getSmfText('buddy_add'),
				'buddyRemove' => $this->getSmfText('buddy_remove'),
			],
			'tabs' => [
				'wall' => $this->getText('tabs_wall'),
				'about' => $this->getText('tabs_about'),
				'activity' => $this->getText('tabs_activity'),
				'buddies' => $this->getText('tabs_buddies'),
			],
			'like' => [
				'like' => $this->getSmfText('like'),
				'unlike' => $this->getSmfText('unlike'),
			],
			'actions' => [
				'like' => $this->getText('action_like'),
				'comment' => $this->getText('action_comment'),
				'delete' => $this->getText('action_delete'),
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
}
