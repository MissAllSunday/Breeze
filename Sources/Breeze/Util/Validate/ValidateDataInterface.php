<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

interface ValidateDataInterface
{
	public function successKeyString(): string;

	public function getSteps(): array;

	public function getData(): array;
}
