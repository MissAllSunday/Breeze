<?php


namespace Breeze\Util;


use Breeze\Breeze;

trait componentsTrait
{
	private static $components = [
		'feed',
	];

	public function getAll(): array
	{
		return static::$components;
	}

	public function loadComponents(array $components = []): void
	{
		$toLoad = array_intersect($this->getAll(), $components);

		foreach ($components as $component)
			loadJavaScriptFile($component, [
				'external' => true,
				'defer' => true,
			], strtolower(Breeze::PATTERN . $component));
	}
}