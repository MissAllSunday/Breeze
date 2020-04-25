<?php


namespace Breeze\Util;


use Breeze\Breeze;

trait CdnTrait
{
	public function getAll(): array
	{
		return [
			'vue' => Breeze::VUE_CDN,
			'axios' => Breeze::AXIOS_CDN,
			'moment' => Breeze::MOMENT_CDN,
		];
	}

	public function loadJsDependencies(array $dependencies = []): void
	{
		$toLoad = empty($dependencies) ? $this->getAll() : array_intersect($this->getAll(), $dependencies);

		foreach ($toLoad as $dependency)
			loadJavaScriptFile($dependency, [
				'external' => true,
				'defer' => true,
			], Breeze::PATTERN . $dependency);
	}
}