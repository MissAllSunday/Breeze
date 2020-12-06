<?php

declare(strict_types=1);

namespace Breeze\Util\Form\Types;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;

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
		$this->setLanguage('ManageSettings');

		$allSelectOptions = [
			SettingsEntity::MOOD_PLACEMENT => array_map(function ($txtKey){
				return $this->getSmfText($txtKey);
			}, SettingsEntity::getProfileFieldsOptions())
		];

		return $allSelectOptions[$settingName] ?? [];
	}
}
