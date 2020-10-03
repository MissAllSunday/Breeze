<?php

declare(strict_types=1);


namespace Breeze\Util\Form;

use Breeze\Breeze;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Traits\TextTrait;

interface UserSettingsBuilderInterface
{
	public const IDENTIFIER = 'Form';

	public function setForm(): void;

	public function display(): string;
}
