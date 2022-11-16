<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

abstract class ValidateActions
{
	public ValidateDataInterface | null $validator = null;

	public array $data = [];

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
		$properties = get_class_vars(static::class);

		if (in_array($action, $properties)) {
			$this->validator = $this->{$action};
		}
	}
}
