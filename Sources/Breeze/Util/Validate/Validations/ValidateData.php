<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

use Breeze\Traits\PersistenceTrait;
use Breeze\Traits\RequestTrait;
use Breeze\Traits\TextTrait;
use Breeze\Util\Json;
use Breeze\Util\Validate\DataNotFoundException;

abstract class ValidateData
{
	use RequestTrait;
	use TextTrait;
	use PersistenceTrait;

	public function set(string $action, $data): void
	{
		$this->{$action}->setData($data);
		$this->{$action}->execute();
	}

	// User
	protected const VALID_USERS = 'areValidUsers';
	protected const VALID_USER = 'validUser';
	protected const SAME_USER = 'isSameUser';
	protected const IGNORE_LIST = 'ignoreList';

	// Data
	protected const DATA_EXISTS = 'dataExists';
	protected const COMPARE = 'compare';
	protected const INT = 'isInt';
	protected const STRING = 'isString';


	protected const FLOOD_CONTROL = 'floodControl';
	protected const PERMISSIONS = 'permissions';
	protected const FEATURE_ENABLE = 'isFeatureEnabled';


	// replace with dataExists
	protected const VALID_STATUS = 'validStatus';
	protected const VALID_COMMENT = 'validComment';


	protected const DEFAULT_STEPS = [
		self::COMPARE,
		self::INT,
		self::STRING,
		self::PERMISSIONS,
	];

	protected array $steps = [
		self::COMPARE,
	];

	protected array $params = [];

	protected array $data;

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

	public function setData(array $data = []): void
	{
		$this->data = array_filter($data === [] ? $this->sanitize(Json::decode(file_get_contents('php://input'))['data']) :
			$data);
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
	 * @throws DataNotFoundException
	 */
	public function isInt(): void
	{
		$integerValues = $this->getInts();
		$data = $this->getData();

		foreach ($integerValues as $integerValueName) {
			if (!is_int($data[$integerValueName])) {
				throw new DataNotFoundException('malformed_data');
			}
		}
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function isString(): void
	{
		$data = $this->getData();
		$strings = $this->getStrings();

		foreach ($strings as $stringValueName) {
			if (!is_string($data[$stringValueName])) {
				throw new DataNotFoundException('malformed_data');
			}
		}
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function isSameUser(): void
	{
		$sessionUser = $this->global('user_info');
		$posterUserId = $this->getPosterId();

		if ($posterUserId === 0 || $posterUserId !== (int)$sessionUser['id']) {
			throw new DataNotFoundException('invalid_users');
		}
	}

	public static function getNameSpace(): string
	{
		return __NAMESPACE__ . '\\';
	}
}
