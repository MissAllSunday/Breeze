<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

abstract class ValidateActions
{
	public ValidateDataInterface $validator;

	public array $data = [];

	public function isValid(): void
	{
		$this->validator->isValid();
	}

	public function getValidator(): ValidateDataInterface
	{
		return $this->validator;
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
		$this->validator = $this->{$action};
	}
}
