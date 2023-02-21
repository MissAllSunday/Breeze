<?php

declare(strict_types=1);

namespace Breeze\Util;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Traits\TextTrait;

class Components
{
	use TextTrait;

	private const FOLDER = 'breezeComponents/';

	private const COMPONENTS = [
		//      'breeze-wall',
		//		'contentSection',
		//		'utils',
		//		'like',
		//		'setMood',
		//		'moodForm',
		//		'modal',
		//		'feed',
		//		'adminMain',
		//		'status',
		//		'comment',
		//		'tabs',
		//		'editor',
		//		'wallMain',
		//		'moodAdmin',
		//		'moodListAdmin',
		//		'textArea',
	];

	private const CDN_JS = [
		'react' => 'https://unpkg.com/react@' . Breeze::REACT_VERSION . '/umd/react.production.min.js',
		'reactDom' => 'https://unpkg.com/react-dom@' . Breeze::REACT_DOM_VERSION . '/umd/react-dom.production.min.js',
	];

	private const CDN_CSS = [
		'editor' => 'https://cdn.jsdelivr.net/npm/suneditor@2.41.3/dist/css/suneditor.min.css',
		//		'noti' => 'https://cdn.jsdelivr.net/npm/vue-toast-notification/dist/theme-default.css',
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
		$this->loadCssDependencies();

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
			'general' => [
				'save' => $this->getText('general_save'),
				'delete' => $this->getText('general_delete'),
				'editing' => $this->getText('general_editing'),
				'close' => $this->getText('general_close'),
				'cancel' => $this->getText('general_cancel'),
				'send' => $this->getText('general_send'),
				'preview' => $this->getText('general_preview'),
				'previewing' => $this->getText('general_previewing'),
				'wrongValues' => $this->getText('error_wrong_values'),
				'errorEmpty' => $this->getText('error_empty'),
			],
			'tabs' => [
				'wall' => $this->getText('tabs_wall'),
				'about' => $this->getText('tabs_about'),
				'activity' => $this->getText('tabs_activity'),
			],
			'mood' => [
				'emoji' => $this->getText('mood_emoji'),
				'description' => $this->getText('mood_description'),
				'enable' => $this->getText('mood_enable'),
				'invalidEmoji' => $this->getText('error_invalidEmoji'),
				'emptyEmoji' => $this->getText('error_emptyEmoji'),
				'moodChange' => $this->getText('moodChange'),
				'newMood' => $this->getText('mood_createNewMood'),
				'sameMood' => $this->getText('error_sameMood'),
				'defaultLabel' => $this->getSetting(
					SettingsEntity::MOOD_LABEL,
					$this->getText(SettingsEntity::MOOD_DEFAULT)
				),
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

	protected function loadCssDependencies(): void
	{
		foreach (self::CDN_CSS as $cssDependency) {
			$this->loadCSSFile($cssDependency, [
				'external' => true,
			], strtolower(Breeze::PATTERN . $cssDependency));
		}
	}
}
