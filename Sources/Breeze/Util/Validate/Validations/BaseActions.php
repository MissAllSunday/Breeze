<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;

abstract class BaseActions
{
	protected const PARAMS = [];

	protected const SUCCESS_KEY = '';

	public array $data;

	public function __construct(
		protected Data $validateData,
		protected User $validateUser,
		protected Allow $validateAllow,
		protected BaseRepositoryInterface $repository
	) {
	}

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
