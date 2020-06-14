<?php

declare(strict_types=1);


namespace Breeze\Util\Validate;

interface ValidateGatewayInterface
{
	public function isValid(): bool;

	public function setValidator(ValidateDataInterface $validator): bool;

	public function setData(): void;

	public function getData(): array;

	public function getNotice(): array;

	public function setNotice(array $notice): void;

	public function response(): array;
}
