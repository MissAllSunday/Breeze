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

	private string $form = '';

	private array $defaultValues;

	public function __construct()
	{
		$this->setTemplate(Breeze::NAME . self::IDENTIFIER);
		$this->userSettingsColumns = UserSettingsEntity::getColumns();
		$this->defaultValues = SettingsEntity::defaultValues();
	}

	public function setForm(array $formOptions, array $formValues = []): void
	{
		foreach ($this->userSettingsColumns as $columnName => $columnType) {
			$formOptions['elements'][] = array_map(function ($formValue) use($columnName, $columnType, $formOptions):
			void{
					if(empty($formValue))
					{
						$formValue = [
							'value' => $this->defaultValues[$columnType],
							'name' => $columnName,
						];
					}
			}, $formValues);
		}




	}

	public function display(): string
	{

	}

	private function callTemplate(string $type): string
	{

	}
}
