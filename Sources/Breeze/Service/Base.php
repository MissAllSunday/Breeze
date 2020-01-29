<?php

declare(strict_types=1);

namespace Breeze\Service;

class Base
{
	public function global(string $variableName)
	{
		return $GLOBALS[$variableName] ?? false;
	}
}
