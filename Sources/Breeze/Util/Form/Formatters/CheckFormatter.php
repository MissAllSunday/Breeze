<?php

declare(strict_types=1);

namespace Breeze\Util\Form\Formatters;

use Breeze\Breeze;
use Breeze\Util\Form\ValueFormatter;
use Breeze\Util\Form\ValueFormatterInterface;

class CheckFormatter extends ValueFormatter implements ValueFormatterInterface
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