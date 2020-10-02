<?php

declare(strict_types=1);


namespace Breeze\Util\Form;

use Breeze\Entity\SettingsEntity;
use Breeze\Traits\TextTrait;
use Breeze\Util\Folder;
use Breeze\Util\Form\Types\ValueFormatter;
use Breeze\Util\Form\Types\ValueFormatterInterface;

class SettingsBuilder
{
	use TextTrait;

	public function getFormatters(): array
	{
		$formatters = [];

		foreach (Folder::getFilesInFolder(__DIR__ . DIRECTORY_SEPARATOR . ValueFormatter::FORMATTER_DIR) as
				 $formatterFile)
		{
			$formatterFileInfo = pathinfo($formatterFile, PATHINFO_FILENAME);

			$formatterKey = strtolower(str_replace(
				ValueFormatter::FORMATTER_TYPE,
				'',
				$formatterFileInfo
			));

			$formatterClassName = ValueFormatter::getNameSpace() . $formatterFileInfo;

			if (ValueFormatter::class === $formatterClassName ||
				ValueFormatterInterface::class === $formatterClassName)
				continue;

			$formatters[$formatterKey] = new $formatterClassName();
		}

		return $formatters;
	}

	public function getConfigVarsSettings(): array
	{
		$configVars = [];
		$allSettings = SettingsEntity::getColumns();
		$formatters = $this->getFormatters();

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
