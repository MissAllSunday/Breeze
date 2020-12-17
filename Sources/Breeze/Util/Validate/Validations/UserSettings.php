<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations;

use Breeze\Entity\UserSettingsEntity;

class UserSettings extends ValidateData implements ValidateDataInterface
{
	protected const SUCCESS_KEY = 'updated_settings';

	public function getSteps(): array
	{
		return [
			'compare',
			'isInt',
			'isString',
		];
	}

	public function getParams(): array
	{
		$defaultValues = UserSettingsEntity::getDefaultValues();

		return array_merge($defaultValues, $this->data);
	}

	public function getData(): array
	{
		return $this->getParams();
	}

	public function getInts(): array
	{
		return array_keys(UserSettingsEntity::getInts());
	}

	public function getStrings(): array
	{
		return array_keys(UserSettingsEntity::getStrings());
	}

	public function getUserIdsNames(): array
	{
		return [];
	}

	public function getPosterId(): int
	{
		return 0;
	}

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}
}