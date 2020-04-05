<?php

declare(strict_types=1);

namespace Breeze\Service;

interface BaseServiceInterface
{
	public function redirect(string $urlName): void;

	public function global(string $variableName);
}
