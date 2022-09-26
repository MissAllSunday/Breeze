<?php

declare(strict_types=1);


namespace Breeze\Util\Validate;

use Breeze\Breeze;
use Breeze\Repository\InvalidDataException;
use Breeze\Traits\TextTrait;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class ValidateGateway implements ValidateGatewayInterface
{
	use TextTrait;

	public const ERROR_TYPE = 'error';
	public const INFO_TYPE = 'info';
	public const SUCCESS_TYPE = 'success';
	public const DEFAULT_ERROR_KEY = self::ERROR_TYPE . '_server';
	protected const BAD_REQUEST = 400;
	protected const UNAUTHORIZED = 401;
	protected const NOT_FOUND = 404;
	protected const METHOD_NOT_ALLOWED = 405;
	protected const NOT_ACCEPTABLE = 406;

	public const MESSAGE_TYPES = [
		self::ERROR_TYPE,
		self::INFO_TYPE,
		self::SUCCESS_TYPE,
	];

	protected array $notice = [
		'type' => self::ERROR_TYPE,
		'message' => self::DEFAULT_ERROR_KEY,
	];

	private ValidateDataInterface $validator;

	protected int $statusCode = 0;

	public function __construct()
	{
		$this->setLanguage(Breeze::NAME);
	}

	public function getStatusCode(): int
	{
		return $this->statusCode;
	}

	public function setValidator(ValidateDataInterface $validator): void
	{
		$this->validator = $validator;
	}

	public function isValid(): bool
	{
		foreach ($this->validator->getSteps() as $step) {
			if (!method_exists($this->validator, $step)) {
				continue;
			}

			try {
				$this->validator->{$step}();
			} catch (InvalidDataException $invalidDataException) {
				$this->statusCode = self::BAD_REQUEST;
				$this->setNotice([
					'message' => sprintf(
						$this->getText(self::DEFAULT_ERROR_KEY),
						$this->getText($invalidDataException->getMessage())
					),
				]);

				return false;
			} catch (DataNotFoundException $dataNotFoundException) {
				$this->statusCode = self::NOT_FOUND;
				$this->setNotice([
					'message' => sprintf(
						$this->getText(self::DEFAULT_ERROR_KEY),
						$this->getText(self::ERROR_TYPE . '_' . $dataNotFoundException->getMessage())
					),
				]);

				return false;
			}
		}

		$this->setNotice([
			'type' => self::SUCCESS_TYPE,
			'message' => $this->getText(self::INFO_TYPE . '_' . $this->validator->successKeyString()),
		]);

		return true;
	}

	public function response(): array
	{
		return $this->notice;
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
