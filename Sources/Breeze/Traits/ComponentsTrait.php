<?php

declare(strict_types=1);


namespace Breeze\Traits;

use Breeze\Breeze;

trait ComponentsTrait
{
	private static $componentsFolder = 'breezeComponents/';

	private static $components = [
		'feed',
		'adminMain',
		'status',
		'comment',
		'tabs',
		'utils',
		'editor',
		'wallMain',
	];

	public function loadComponents(array $components = []): void
	{
		$componentsToLoad = array_intersect(self::$components, $components);

		foreach ($componentsToLoad as $component)
			loadJavaScriptFile(self::$componentsFolder . $component . '.js', [
				'defer' => true,
				'default_theme' => true,
			], strtolower(Breeze::PATTERN . $component));
	}
}
