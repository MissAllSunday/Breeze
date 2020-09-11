<?php

declare(strict_types=1);

namespace Breeze\Util\Form\Formatters;

use Breeze\Breeze;

class IntFormatter extends ValueFormatter implements ValueFormatterInterface
{
	public function getConfigVar(string $settingName, string $settingType): array
	{
		return [
			$settingType,
			Breeze::PATTERN . $settingName,
			'size' => 3,
			'subtext' => $this->getText($settingName . '_sub'),
		];
	}
}
