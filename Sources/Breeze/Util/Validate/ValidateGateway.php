<?php

declare(strict_types=1);


namespace Breeze\Util\Validate;

use Breeze\Breeze;
use Breeze\Service\UserServiceInterface;
use Breeze\Traits\TextTrait;

class ValidateGateway implements ValidateGatewayInterface
{
	use TextTrait;

	public const ERROR_TYPE = 'error';
	public const NOTICE_TYPE = 'notice';
	public const INFO_TYPE = 'info';
	public const DEFAULT_ERROR_KEY = self::ERROR_TYPE . '_server';

	public const MESSAGE_TYPES = [
		self::ERROR_TYPE,
		self::NOTICE_TYPE,
		self::INFO_TYPE,
	];

	protected $notice = [
		'type' => self::ERROR_TYPE,
		'message' => self::DEFAULT_ERROR_KEY,
	];

	protected $data = [];

	/**
	 * @var UserServiceInterface
	 */
	private $userService;

	/**
	 * @var ValidateDataInterface
	 */
	private $validator;

	public function __construct(UserServiceInterface $userService)
	{
		$this->setLanguage(Breeze::NAME);

		$this->userService = $userService;
	}

	public function isValid(): bool
	{
		foreach ($this->validator->getSteps() as $step)
		{
			if (!method_exists($this->validator, $step))
				continue;

			try {
				$this->{$step}();
			} catch (ValidateDataException $e) {
				$this->setNotice([
					'message' => sprintf(
						$this->getText(self::DEFAULT_ERROR_KEY),
						$this->getText(self::ERROR_TYPE . '_' . $e->getMessage())
					),
				]);

				return false;
			}
		}

		$this->setNotice([
			'type' => self::INFO_TYPE,
			'message' => $this->getText($this->validator->successKeyString())
		]);

		return true;
	}

	public function setValidator(string $validatorName): bool
	{
		$validatorName = ucfirst($validatorName);

		if (!is_subclass_of($validatorName, ValidateDataInterface::class))
			throw new ValidateDataException('error_no_validator');

		$this->validator = new $validatorName();

		return true;
	}

	public function response(): array
	{
		return $this->notice;
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

	public function getNotice(): array
	{
		return $this->notice;
	}

	public function setNotice(array $notice): void
	{
		$this->notice = array_merge($this->notice, $notice);
	}
}
