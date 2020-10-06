<?php

declare(strict_types=1);


namespace Breeze\Util\Form;

interface UserSettingsBuilderInterface
{
	public const IDENTIFIER = 'Form';

	public function setForm(array $formOptions, array $formValues): void;

	public function display(): string;
}
