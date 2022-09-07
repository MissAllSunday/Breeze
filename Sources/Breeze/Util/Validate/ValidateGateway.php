<?php

declare(strict_types=1);


namespace Breeze\Util\Validate;

use Breeze\Breeze;
use Breeze\Exceptions\InvalidCommentException;
use Breeze\Exceptions\InvalidException;
use Breeze\Exceptions\InvalidLikeException;
use Breeze\Exceptions\InvalidMoodException;
use Breeze\Exceptions\InvalidStatusException;
use Breeze\Traits\TextTrait;
use Breeze\Util\Json;
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

	protected array $data = [];

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

	public function setValidator(ValidateDataInterface $validator): bool
	{
		$this->validator = $validator;

		$this->validator->setData($this->data);

		return true;
	}

	public function isValid(): bool
	{
		foreach ($this->validator->getSteps() as $step) {
			if (!method_exists($this->validator, $step)) {
				continue;
			}

			try {
				$this->data = $this->validator->getData();
				$this->validator->{$step}();
			} catch (InvalidException $invalidException) {
				$this->statusCode = self::NOT_FOUND;
				$this->setNotice([
					'message' => sprintf(
						$this->getText(self::DEFAULT_ERROR_KEY),
						$this->getText($invalidException->getMessage())
					),
				]);

				return false;
			} catch (ValidateDataException $validateDataException) {
				$this->statusCode = self::BAD_REQUEST;
				$this->setNotice([
					'message' => sprintf(
						$this->getText(self::DEFAULT_ERROR_KEY),
						$this->getText(self::ERROR_TYPE . '_' . $validateDataException->getMessage())
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

	public function setData(array $rawData = []): void
	{
		$rawData = !empty($rawData) ?
			$rawData : Json::decode(file_get_contents('php://input'));

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
