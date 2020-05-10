<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

use \Breeze\Traits\RequestTrait;
use Breeze\Service\UserServiceInterface;
use Breeze\Traits\TextTrait;

abstract class ValidateData
{
	use RequestTrait;
	use TextTrait;

	public const ERROR_TYPE = 'error';
	public const NOTICE_TYPE = 'notice';
	public const INFO_TYPE = 'info';
	public const DEFAULT_ERROR_KEY = 'error_server';

	public const MESSAGE_TYPES = [
		self::ERROR_TYPE,
		self::NOTICE_TYPE,
		self::INFO_TYPE,
	];

	protected const STEPS = [
		'clean',
		'isInt',
		'isString',
		'areValidUsers'
	];

	public $data = [];

	protected $errorKey = '';

	/**
	 * @var UserServiceInterface
	 */
	private $userService;

	public function __construct(UserServiceInterface $userService)
	{
		$this->userService = $userService;
	}

	public abstract function getParams(): array;

	public abstract function getInts(): array;

	public abstract function getStrings(): array;

	public abstract function getUserIdsNames(): array;

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

	public function isInt(): bool
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

		foreach ($this->getStrings() as $stringValueName)
		{
			$isString = is_string($this->data[$stringValueName]);

			if (!$isString)
			{
				$this->errorKey = 'malformed_data';

					break;
			}
		}

		return $isString;
	}

	public function areValidUsers(): bool
	{
		$usersIds = array_map(
			function ($intName){
			return $this->data[$intName];
		},
			$this->getUserIdsNames()
		);

		$loadedUsers = $this->userService->loadUsersInfo($usersIds, true);
		$invalidUsers = array_diff_key(array_flip($usersIds), $loadedUsers);

		if (!empty($invalidUsers))
			$this->errorKey = 'invalid_users';

		return empty($invalidUsers);
	}

	public function response(): array
	{
		return [
			'type' => self::ERROR_TYPE,
			'message' => $this->errorKey,
			'data' => [],
		];
	}

	public function getRawData(): void
	{
		$rawData = json_decode(file_get_contents('php://input'), true) ?? [];
		$this->data = array_filter($rawData);
	}

	public function setData(array $data): void
	{
		$this->data = $data;
	}

	public function getData(): array
	{
		return $this->data;
	}

	public function getErrorKey(): string
	{
		return $this->errorKey;
	}

	protected function compare(): bool
	{
		$isArraySizeEqual = true;

		$isArraySizeEqual = empty(array_diff_key($this->getParams(), $this->data));

		if (!$isArraySizeEqual)
			$this->errorKey = 'incomplete_data';

		return $isArraySizeEqual;
	}
}
