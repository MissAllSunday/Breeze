<?php

declare(strict_types=1);

namespace Breeze\Util\Form\Types;

use Breeze\Breeze;

class SelectType extends ValueFormatter implements ValueFormatterInterface
{
	public function getConfigVar(string $settingName, string $settingType): array
	{
		return [
			$settingType,
			Breeze::PATTERN . $settingName,
			$this->getSettingOptions($settingName),
			'subtext' => $this->getText($settingName . '_sub'),
			'multiple' => false,
		];
	}

	private function getSettingOptions(string $settingName): array
	{
		return [];
	}
}
