<?php

declare(strict_types=1);


namespace Breeze\Util\Form;

interface SettingsBuilderInterface
{
	public function getFormatters(): array;

	public function getConfigVarsSettings(): array;
}
