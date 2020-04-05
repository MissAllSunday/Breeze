<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\SettingsEntity;
use Breeze\Util\Form\ValueFormatter;
use Breeze\Util\Form\ValueFormatterInterface;

class FormService extends BaseService implements ServiceInterface
{
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

	public function getCoverConfigVarsSettings(): array
	{
		return [];
	}
}
