<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

abstract class ValidateActions
{
	public ValidateDataInterface | null $validator = null;

	public array $data = [];

	/**
	 * Explicit sub-action => validator map.
	 *
	 * A `null` value is a deliberate no-op: the sub-action is known and needs no
	 * validation. An absent key means the sub-action is unknown to this composite
	 * and no validator will be bound.
	 *
	 * @return array<string, ValidateDataInterface | null>
	 */
	abstract public function validators(): array;

	public function isValid(): void
	{
		$this->validator?->isValid();
	}

	public function setUp(array $data, string $action): void
	{
		$this->setData($data);
		$this->setValidator($action);
	}

	public function setData(array $data): void
	{
		$this->data = $data;
	}

	public function setValidator(string $action): void
	{
		$this->validator = $this->validators()[$action] ?? null;
		$this->validator?->setData($this->data);
	}
}
