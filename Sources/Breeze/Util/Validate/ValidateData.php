<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

use \Breeze\Traits\RequestTrait;
use Breeze\Traits\TextTrait;

abstract class ValidateData
{
	use RequestTrait;
	use TextTrait;

	public const ERROR_TYPE = 'error';
	public const NOTICE_TYPE = 'notice';
	public const INFO_TYPE = 'info';

	public const MESSAGE_TYPES = [
		self::ERROR_TYPE,
		self::NOTICE_TYPE,
		self::INFO_TYPE,
	];

	protected const STEPS = [
		'compare',
		'clean',
		'isInt',
		'isString',
	];

	public $data = [];

	protected $errorKey = 'error_server';

	public abstract function getParams(): array;

	public function getSteps(): array
	{
		return self::STEPS;
	}

	public function isValid(): bool
	{
		$isValid = true;

		foreach ($this->getSteps() as $step)
			if (!$this->{$step}())
			{
				$isValid = false;

				break;
			}

		return $isValid;
	}

	public function clean(): bool
	{
		$this->data = array_filter($this->sanitize($this->data));

		return $this->compare();
	}

	public function compare(): bool
	{
		return empty(array_diff_key($this->getParams(), $this->data));
	}

	public function isInt():bool
	{
		$isInt = true;

		foreach ($this->getInts() as $integerValueName)
		{
			$isInt = is_int($this->data[$integerValueName]);

			if (!$isInt)
			{
				$this->errorKey = 'malformed_data';

				break;
			}
		}

		return $isInt;
	}

	public function isString():bool
	{
		$isString = true;

		foreach ($this->getInts() as $integerValueName)
		{
			$isString = is_string($this->data[$integerValueName]);

			if (!$isString)
			{
				$this->errorKey = 'malformed_data';

					break;
			}
		}

		return $isString;
	}

	public function response(): array
	{
		return [
			'type' => self::ERROR_TYPE,
			'message' => $this->errorKey,
			'data' => [],
		];
	}

	public function setData(): void
	{
		$rawData = json_decode(file_get_contents('php://input'), true) ?? [];
		$this->data = array_filter($rawData);
	}

	public function getData(): array
	{
		return $this->data;
	}
}
