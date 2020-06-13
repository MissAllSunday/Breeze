<?php

declare(strict_types=1);


namespace Breeze\Util\Validate;

interface ValidateGatewayInterface
{
	public function setValidator(string $validatorName): bool;

	public function setData(): void;

	public function getData(): array;
}
