<?php

declare(strict_types=1);

namespace Breeze\Util\Form\Types;

interface ValueFormatterInterface
{
	public function getConfigVar(string $settingName, string $settingType): array;
}
