<?php

declare(strict_types=1);


namespace Breeze\Util\Validate;

use Breeze\Util\Validate\Validations\ValidateDataInterface;

interface ValidateGatewayInterface
{
	public function isValid(): bool;

	public function getStatusCode(): int;

	public function setValidator(ValidateDataInterface $validator): void;

	public function getNotice(): array;

	public function setNotice(array $notice): void;

	public function response(): array;
}
