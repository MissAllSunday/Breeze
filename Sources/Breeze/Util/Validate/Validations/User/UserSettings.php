<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\User;

use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;

class UserSettings extends BaseActions implements ValidateDataInterface
{
	protected const SUCCESS_KEY = 'updated_settings';

	public function __construct(
		protected Data $validateData,
		protected Allow $validateAllow,
		protected User $validateUser,
		protected BaseRepositoryInterface $repository
	) {
	}

	public function getParams(): array
	{
		$defaultValues = UserSettingsEntity::getDefaultValues();

		return array_merge($defaultValues, $this->data);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function isValid(): void
	{
		$this->validateData->compare(UserSettingsEntity::getDefaultValues(), $this->getParams());
	}
}
