<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

abstract class ValidateData
{
	use \Breeze\Traits\RequestTrait;

	public $data = [];

	public function __construct()
	{
		$this->setData();
	}

	public abstract function getSteps(): array;

	public abstract function getParams(): array;

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

	public function setData(): void
	{
		$this->data = array_filter(json_decode(file_get_contents('php://input'), true));
	}

	public function getData(): array
	{
		return $this->data;
	}
}
