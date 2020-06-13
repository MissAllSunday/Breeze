<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

use \Breeze\Traits\RequestTrait;
use Breeze\Entity\SettingsEntity;
use Breeze\Traits\PersistenceTrait;

abstract class ValidateData
{
	use RequestTrait;
	use PersistenceTrait;

	protected const ALL_STEPS = [
		'clean',
		'compare',
		'isInt',
		'isString',
		'areValidUsers',
		'floodControl',
	];

	protected $steps = [];

	protected $params = [];

	public abstract function getParams(): array;

	public abstract function getInts(): array;

	public abstract function getStrings(): array;

	public abstract function getUserIdsNames(): array;

	public abstract function getPosterId(): int;

	public abstract function successKeyString(): string;

	public function getSteps(): array
	{
		return $this->steps ?? self::ALL_STEPS;
	}

	public function clean(): void
	{
		$this->data = array_filter($this->sanitize($this->data));
	}

	public function isInt(): void
	{
		foreach ($this->getInts() as $integerValueName)
			if (!is_int($this->data[$integerValueName]))
				throw new ValidateDataException('malformed_data');
	}

	public function isString(): void
	{
		foreach ($this->getStrings() as $stringValueName)
			if (!is_string($this->data[$stringValueName]))
				throw new ValidateDataException('malformed_data');
	}

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

	public function response(): array
	{
		return $this->notice;
	}

	protected function compare(): void
	{
		if (!empty(array_diff_key($this->getParams(), $this->data)))
			throw new ValidateDataException('incomplete_data');
	}
}
