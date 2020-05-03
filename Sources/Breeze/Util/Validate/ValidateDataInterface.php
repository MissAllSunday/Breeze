<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

interface ValidateDataInterface
{
	public function isValid(): bool;

	public function getSteps(): array;

	public function getParams(): array;

	public function response(): array;

	public function setData(array $data): void;

	public function getData(): array;

	public function getRawData(): void;

	public function getInts(): array;

	public function getStrings(): array;
}
