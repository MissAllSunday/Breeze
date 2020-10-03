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

	private string $form = '';

	public function __construct(array $formValues = [])
	{
		$this->setTemplate(Breeze::NAME . self::IDENTIFIER);
		$this->userSettingsColumns = UserSettingsEntity::getColumns();

		$this->formValues = $formValues;
	}

	public function setForm(): void
	{
		foreach ($this->userSettingsColumns as $columnName => $columnType) {
			$this->form .= array_map(function ($formValue) use($columnName, $columnType){

			}, $this->formValues);
		}




	}

	public function display(): string
	{

	}

	private function callTemplate(string $type): string
	{

	}
}
