<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

use \Breeze\Traits\RequestTrait;
use Breeze\Entity\SettingsEntity;
use Breeze\Service\UserServiceInterface;
use Breeze\Traits\PersistenceTrait;
use Breeze\Traits\TextTrait;
use Breeze\Util\Validate\ValidateDataException;

abstract class ValidateData
{
	use RequestTrait;
	use TextTrait;
	use PersistenceTrait;

	protected const ALL_STEPS = [
		'compare',
		'isInt',
		'isString',
		'areValidUsers',
		'floodControl',
	];

	protected $steps = [];

	protected $params = [];

	protected $data;

	/**
	 * @var UserServiceInterface
	 */
	protected $userService;

	public function __construct(UserServiceInterface $userService)
	{
		$this->userService = $userService;
	}

	public abstract function getParams(): array;

	public abstract function getInts(): array;

	public abstract function getStrings(): array;

	public abstract function getUserIdsNames(): array;

	public abstract function getPosterId(): int;

	public abstract function successKeyString(): string;

	public function getData(): array
	{
		return $this->data;
	}

	public function setData(array $data): void
	{
		$this->data = $data;
	}

	public function getSteps(): array
	{
		return $this->steps ?? self::ALL_STEPS;
	}

	/**
	 * @throws ValidateDataException
	 */
	public function clean(): void
	{
		$this->data = array_filter($this->sanitize($this->data));

		$this->compare();
	}

	/**
	 * @throws ValidateDataException
	 */
	public function isInt(): void
	{
		foreach ($this->getInts() as $integerValueName)
			if (!is_int($this->data[$integerValueName]))
				throw new ValidateDataException('malformed_data');
	}

	/**
	 * @throws ValidateDataException
	 */
	public function isString(): void
	{
		foreach ($this->getStrings() as $stringValueName)
			if (!is_string($this->data[$stringValueName]))
				throw new ValidateDataException('malformed_data');
	}

	/**
	 * @throws ValidateDataException
	 */
	public function areValidUsers(): void
	{
		$usersIds = array_map(
			function ($intName){
			return $this->data[$intName];
		},
			$this->getUserIdsNames()
		);

		$loadedUsers = $this->userService->getUsersToLoad($usersIds);

		if (array_diff($usersIds, $loadedUsers))
			throw new ValidateDataException('invalid_users');
	}

	/**
	 * @throws ValidateDataException
	 */
	public function floodControl(): void
	{
		$posterId = $this->getPosterId();
		$seconds = 60 * ($this->getSetting(SettingsEntity::MAX_FLOOD_MINUTES, 5));
		$messages = $this->getSetting(SettingsEntity::MAX_FLOOD_NUM, 10);
		$floodKeyName = 'flood_' . $posterId;

		$floodData = $this->getPersistenceValue($floodKeyName);

		if (empty($floodData))
			$floodData = [
				'time' => time() + $seconds,
				'msgCount' => 0,
			];

		$floodData['msgCount']++;

		// Chatty one huh?
		if ($floodData['msgCount'] >= $messages && time() <= $floodData['time'])
			throw new ValidateDataException('flood');

		if (time() >= $floodData['time'])
			$this->unsetPersistenceValue($floodKeyName);
	}

	/**
	 * @throws ValidateDataException
	 */
	public function compare(): void
	{
		if (!empty(array_diff_key($this->getParams(), $this->data)))
			throw new ValidateDataException('incomplete_data');
	}

	public static function getNameSpace(): string
	{
		return __NAMESPACE__ . '\\';
	}
}
