<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

interface ActionsInterface
{
	public function setData(array $data): void;

	public function execute(): void;
}
