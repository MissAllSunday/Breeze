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
		'utils',
		'like',
		'setMood',
		'moodForm',
		'modal',
		'feed',
		'adminMain',
		'status',
		'comment',
		'tabs',
		'editor',
		'wallMain',
		'moodAdmin',
		'moodListAdmin',
		'textArea',
	];

	private const CDN_JS = [
		'vue' => 'https://cdn.jsdelivr.net/npm/vue@' . Breeze::VUE_VERSION . '/dist/vue.js',
		'axios' => 'https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js',
		'moment' => 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js',
		'noti' => 'https://cdn.jsdelivr.net/npm/vue-toast-notification',
		'editor' => 'https://cdn.jsdelivr.net/npm/suneditor@latest/dist/suneditor.min.js',
		'purify' => 'https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.0.14/purify.min.js',
	];

	private const CDN_CSS = [
		'editor' => 'https://cdn.jsdelivr.net/npm/suneditor@latest/dist/css/suneditor.min.css',
		'noti' => 'https://cdn.jsdelivr.net/npm/vue-toast-notification/dist/theme-default.css',
	];

	public function loadComponents(array $components = []): void
	{
		static $alreadyLoaded = [];

		$componentsToLoad = array_intersect(self::COMPONENTS, $components);

		$this->loadJsDependencies();
		$this->loadCssDependencies();

		foreach ($componentsToLoad as $component) {
			if (isset($alreadyLoaded[$component]) && true === $alreadyLoaded[$component]) {
				continue;
			}

			$this->loadJavaScriptFile(self::FOLDER . $component . '.js', [
				'defer' => true,
				'default_theme' => true,
			], strtolower(Breeze::PATTERN . $component));

			$alreadyLoaded[$component] = true;
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
		static $alreadyDone = false;

		if (!$alreadyDone) {
			foreach (self::CDN_JS as $jsDependency) {
				var_dump($jsDependency);
				$this->loadJavaScriptFile($jsDependency, [
					'external' => true,
					'defer' => true,
				], strtolower(Breeze::PATTERN . $jsDependency));
			}

			$alreadyDone = true;
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
