<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

use \Breeze\Traits\RequestTrait;
use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
use Breeze\Service\UserServiceInterface;
use Breeze\Traits\PersistenceTrait;
use Breeze\Traits\TextTrait;

abstract class ValidateData
{
	use RequestTrait;
	use TextTrait;
	use PersistenceTrait;

	private const ERROR_PREFIX = 'error_';
	public const ERROR_TYPE = 'error';
	public const NOTICE_TYPE = 'notice';
	public const INFO_TYPE = 'info';
	public const DEFAULT_ERROR_KEY = self::ERROR_PREFIX . 'server';

	public const MESSAGE_TYPES = [
		self::ERROR_TYPE,
		self::NOTICE_TYPE,
		self::INFO_TYPE,
	];

	protected const STEPS = [
		'clean',
		'isInt',
		'isString',
		'areValidUsers',
		'floodControl',
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

	public abstract function getPosterId(): int;

	public function getSteps(): array
	{
		return self::STEPS;
	}

	public function isValid(): bool
	{
		$isValid = false;

		foreach ($this->getSteps() as $step)
		{
			if (!method_exists($this, $step))
				continue;

			try {
				$this->{$step}();
				$isValid = true;
			} catch (ValidateDataException $e) {
				$this->setErrorKey($e->getMessage());

				break;
			} catch (\InvalidArgumentException $e){
				$this->setErrorKey($e->getMessage());

				break;
			}
		}

		return $isValid;
	}

	public function clean(): void
	{
		$this->data = array_filter($this->sanitize($this->data));

		$this->compare();
	}

	public function isInt(): void
	{
		foreach ($this->getInts() as $integerValueName)
			if (!is_int($this->data[$integerValueName]))
				throw new \InvalidArgumentException('malformed_data');
	}

	public function isString(): void
	{
		foreach ($this->getStrings() as $stringValueName)
			if (!is_string($this->data[$stringValueName]))
				throw new \InvalidArgumentException('malformed_data');
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

		if (in_array(false, $loadedUsers))
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
		$this->setLanguage(Breeze::NAME);

		return [
			'type' => self::ERROR_TYPE,
			'message' => $this->getText(self::ERROR_PREFIX . $this->errorKey),
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

	protected function setErrorKey(string $errorKey): void
	{
		$this->errorKey = $errorKey;
	}

	protected function compare(): void
	{
		if (!empty(array_diff_key($this->getParams(), $this->data)))
			throw new \InvalidArgumentException('incomplete_data');
	}
}
