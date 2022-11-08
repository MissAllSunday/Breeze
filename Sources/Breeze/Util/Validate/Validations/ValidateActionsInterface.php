<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

use Breeze\Repository\InvalidDataException;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;

interface ValidateActionsInterface
{
	public function getValidator(): ValidateDataInterface;

	public function setUp(array $data, string $subAction): void;

	/**
	 * @throws InvalidDataException
	 * @throws DataNotFoundException
	 * @throws NotAllowedException
	 */
	public function isValid(): void;
}
