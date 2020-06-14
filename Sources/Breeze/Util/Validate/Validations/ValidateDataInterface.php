<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

interface ValidateDataInterface
{
	public function successKeyString(): string;

	public function getSteps(): array;

	public function getData(): array;

	public function setData(array $data): void;
}
