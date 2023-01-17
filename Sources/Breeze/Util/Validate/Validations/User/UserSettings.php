<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\User;

use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\InvalidDataException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class UserSettings extends BaseActions implements ValidateDataInterface
{
	protected const SUCCESS_KEY = 'updated_settings';

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}

	public function getParams(): array
	{
		$defaultValues = UserSettingsEntity::getDefaultValues();

		return array_merge($defaultValues, $this->data);
	}

	/**
	 * @throws InvalidDataException
	 */
	public function isValid(): void
	{
		$this->validateData->compare(UserSettingsEntity::getDefaultValues(), $this->getParams());
	}
}
