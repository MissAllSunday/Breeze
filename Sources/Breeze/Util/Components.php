<?php

declare(strict_types=1);

namespace Breeze\Util;

use Breeze\Breeze;

class Components
{
	private const FOLDER = 'breezeComponents/';
	private const COMPONENTS = [
		'modal',
		'feed',
		'adminMain',
		'status',
		'comment',
		'tabs',
		'utils',
		'editor',
		'wallMain',
		'mood',
		'moodList',
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
		$componentsToLoad = array_intersect(self::COMPONENTS, $components);

		$this->loadJsDependencies();
		$this->loadCssDependencies();

		foreach ($componentsToLoad as $component) {
			$this->loadJavaScriptFile(self::FOLDER . $component . '.js', [
				'defer' => true,
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

	protected function loadJsDependencies(): void
	{
		foreach (self::CDN_JS as $jsDependency) {
			$this->loadJavaScriptFile($jsDependency, [
				'external' => true,
				'defer' => true,
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
