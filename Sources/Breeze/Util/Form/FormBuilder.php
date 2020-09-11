<?php

declare(strict_types=1);


namespace Breeze\Util;

use Breeze\Entity\SettingsEntity;
use Breeze\Traits\TextTrait;
use Breeze\Util\Form\Formatters\ValueFormatter;
use Breeze\Util\Form\Formatters\ValueFormatterInterface;

class FormBuilder
{
	use TextTrait;

	public function getConfigVarsSettings(): array
	{
		$configVars = [];
		$allSettings = SettingsEntity::getColumns();
		$formatters = ValueFormatter::getFormatters();

		foreach ($allSettings as $settingName => $settingType)
			if (!empty($formatters[$settingType]))
			{
				/** @var ValueFormatterInterface $formatter */
				$formatter = $formatters[$settingType];

				$configVars[] = $formatter->getConfigVar($settingName, $settingType);
			}

		return $configVars;
	}
}
