<?php

declare(strict_types=1);

namespace Breeze\Util\Form\Types;

use Breeze\Breeze;

class CheckType extends ValueFormatter implements ValueFormatterInterface
{
	public function getConfigVar(string $settingName, string $settingType): array
	{
		return [
			$settingType,
			Breeze::PATTERN . $settingName,
			'subtext' => $this->getText($settingName . '_sub')
		];
	}
}
