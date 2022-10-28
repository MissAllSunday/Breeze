<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

abstract class BaseActions
{
	protected const PARAMS = [];

	protected const SUCCESS_KEY = '';

	public array $data;

	public function setData(array $data = []): void
	{
		$this->data = $data;
	}

	public function getParams(): array
	{
		return static::PARAMS;
	}

	public function successKeyString(): string
	{
		return static::SUCCESS_KEY;
	}
}
