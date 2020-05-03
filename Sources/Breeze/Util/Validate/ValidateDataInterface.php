<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

interface ValidateDataInterface
{
	public function isValid(): bool;

	public function getSteps(): array;

	public function getParams(): array;

	public function response(): array;

	public function setData(): void;

	public function getData(): array;
}
