<?php

declare(strict_types=1);


namespace Breeze\Traits;

use Breeze\Breeze;

class Cdn
{
	public const VUE_CDN = 'https://cdn.jsdelivr.net/npm/vue@' . Breeze::VUE_VERSION . '/dist/vue.js';
	public const AXIOS_CDN = 'https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js';
	public const MOMENT_CDN = 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js';
	public const NOTI_CDN = 'https://cdn.jsdelivr.net/npm/vue-toast-notification';
	public const EDITOR_CDN = 'https://cdn.jsdelivr.net/npm/suneditor@latest/dist/suneditor.min.js';
	public const EDITOR_CSS = 'https://cdn.jsdelivr.net/npm/suneditor@latest/dist/css/suneditor.min.css';

	public function getAllJsCalls(): array
	{
		return [
			'vue' => self::VUE_CDN,
			'axios' => self::AXIOS_CDN,
			'moment' => self::MOMENT_CDN,
			'noti' => self::NOTI_CDN,
			'editor' => self::EDITOR_CDN,
		];
	}

	public function getAllCssCalls(): array
	{
		return [
			'editor' => self::EDITOR_CSS,
		];
	}

	public function loadJsDependencies(array $dependencies = []): void
	{
		$allJs = $this->getAllJsCalls();

		$toLoad = empty($dependencies) ? $allJs : array_intersect($allJs, $dependencies);

		foreach ($toLoad as $dependency)
			loadJavaScriptFile($dependency, [
				'external' => true,
				'defer' => true,
			], strtolower(Breeze::PATTERN . $dependency));
	}

	public function loadCssDependencies(array $dependencies = []): void
	{
		$allJs = $this->getAllCssCalls();

		$toLoad = empty($dependencies) ? $allJs : array_intersect($allJs, $dependencies);

		foreach ($toLoad as $dependency)
			loadCSSFile($dependency, [
				'external' => true,
			], strtolower(Breeze::PATTERN . $dependency));
	}
}
