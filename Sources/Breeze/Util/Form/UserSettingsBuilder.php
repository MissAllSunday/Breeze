<?php

declare(strict_types=1);


namespace Breeze\Util\Form;

use Breeze\Breeze;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Traits\TextTrait;

class UserSettingsBuilder
{
	use TextTrait;

	public const IDENTIFIER = 'Form';

	private array $userSettingsColumns;

	private array $formValues;

	public function __construct(array $formValues)
	{
		$this->setTemplate(Breeze::NAME . self::IDENTIFIER);
		$this->userSettingsColumns = UserSettingsEntity::getColumns();

		$this->formValues = $formValues;
	}

	public function setForm(): void
	{
		foreach ($this->userSettingsColumns as $columnName => $columnType)
		{

		}



	}
}
