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

	protected const CLEAN = 'clean';

	protected const COMPARE = 'compare';

	protected const INT = 'isInt';

	protected const STRING = 'isString';

	protected const VALID_USERS = 'areValidUsers';

	protected const VALID_USER = 'validUser';

	protected const FLOOD_CONTROL = 'floodControl';

	protected const PERMISSIONS = 'permissions';

	protected const VALID_STATUS = 'validStatus';

	protected const VALID_COMMENT = 'validComment';

	protected const IGNORE_LIST = 'ignoreList';

	protected const DATA_EXISTS = 'dataExists';

	protected const DEFAULT_STEPS = [
		self::CLEAN,
		self::INT,
		self::STRING,
		self::PERMISSIONS,
	];

	protected array $steps = [
		self::CLEAN,
	];

	protected array $params = [];

	protected array $data;

	protected UserServiceInterface $userService;

	public function __construct(UserServiceInterface $userService)
	{
		$this->userService = $userService;
	}

	abstract public function getParams(): array;

	abstract public function getInts(): array;

	abstract public function getStrings(): array;

	abstract public function getUserIdsNames(): array;

	abstract public function getPosterId(): int;

	abstract public function successKeyString(): string;

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
		return $this->steps ?? self::DEFAULT_STEPS;
	}

	public function setSteps(array $customSteps): void
	{
		$this->steps = $customSteps;
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
		$integerValues = $this->getInts();
		$data = $this->getData();

		foreach ($integerValues as $integerValueName) {
			if (!is_int($data[$integerValueName])) {
				throw new ValidateDataException('malformed_data');
			}
		}
	}

	/**
	 * @throws ValidateDataException
	 */
	public function isString(): void
	{
		$data = $this->getData();
		$strings = $this->getStrings();

		foreach ($strings as $stringValueName) {
			if (!is_string($data[$stringValueName])) {
				throw new ValidateDataException('malformed_data');
			}
		}
	}

	/**
	 * @throws ValidateDataException
	 */
	public function areValidUsers(): void
	{
		$usersIds = array_map(
			function ($intName) {
				return $this->data[$intName];
			},
			$this->getUserIdsNames()
		);

		$loadedUsers = $this->userService->getUsersToLoad($usersIds);

		if (array_diff($usersIds, $loadedUsers)) {
			throw new ValidateDataException('invalid_users');
		}
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

		if (empty($floodData)) {
			$floodData = [
				'time' => time() + $seconds,
				'msgCount' => 0,
			];
		}

		$floodData['msgCount']++;

		// Chatty one huh?
		if ($floodData['msgCount'] >= $messages && time() <= $floodData['time']) {
			throw new ValidateDataException('flood');
		}

		if (time() >= $floodData['time']) {
			$this->unsetPersistenceValue($floodKeyName);
		}
	}

	/**
	 * @throws ValidateDataException
	 */
	public function compare(): void
	{
		if (!empty(array_diff_key($this->getParams(), $this->getData()))) {
			throw new ValidateDataException('incomplete_data');
		}
	}

	public static function getNameSpace(): string
	{
		return __NAMESPACE__ . '\\';
	}
}
