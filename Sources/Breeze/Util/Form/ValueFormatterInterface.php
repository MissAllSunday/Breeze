<?php

declare(strict_types=1);

namespace Breeze\Util\Form;

interface ValueFormatterInterface
{
	public function getConfigVar(string $settingName, string $settingType): array;
}
