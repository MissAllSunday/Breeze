<?php

declare(strict_types=1);


namespace Breeze\Util\Form;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Traits\TextTrait;

class UserSettingsBuilder implements UserSettingsBuilderInterface
{
	use TextTrait;

	private array $userSettingsColumns;

	private array $defaultValues;

	private array $formOptions;

	public function __construct()
	{
		$this->setTemplate(Breeze::NAME . self::IDENTIFIER);
		$this->userSettingsColumns = UserSettingsEntity::getColumns();
		$this->defaultValues = SettingsEntity::defaultValues();
	}

	public function setForm(array $formOptions, array $formValues = []): void
	{
		$this->formOptions = $formOptions;

		foreach ($this->userSettingsColumns as $columnName => $columnType) {
			$formOptions['elements'][] = array_map(function ($formValue) use($columnName, $columnType)
			{
				return [
					'text' => '',
					'desc' => '',
					'name' => $columnName,
					'value' => !empty($formValue) ? $formValue : $this->defaultValues[$columnType],
				];

			}, $formValues);
		}
	}

	public function display(): string
	{
		return template_breezeForm_Display($this->formOptions);
	}
}
