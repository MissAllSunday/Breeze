<?php

declare(strict_types=1);


namespace Breeze\Service;

interface ServiceInterface
{
	public function global(string $variableName);

	public function setGlobal($globalName, $globalValue): void;
}
