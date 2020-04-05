<?php

declare(strict_types=1);

namespace Breeze\Util\Form\Formatters;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Util\Form\ValueFormatter;
use Breeze\Util\Form\ValueFormatterInterface;

class SelectFormatter extends ValueFormatter implements ValueFormatterInterface
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
		$allSelectOptions = [
			SettingsEntity::MOOD_PLACEMENT => [
				$this->getSmfText('custom_profile_placement_standard'),
				$this->getSmfText('custom_profile_placement_icons'),
				$this->getSmfText('custom_profile_placement_above_signature'),
				$this->getSmfText('custom_profile_placement_below_signature'),
				$this->getSmfText('custom_profile_placement_below_avatar'),
				$this->getSmfText('custom_profile_placement_above_member'),
				$this->getSmfText('custom_profile_placement_bottom_poster'),
			]
		];

		return $allSelectOptions[$settingName] ?? [];
	}
}
