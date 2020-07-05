<?php

declare(strict_types=1);


namespace Breeze\Util\Validate;

use Breeze\Breeze;
use Breeze\Repository\InvalidCommentException;
use Breeze\Repository\InvalidStatusException;
use Breeze\Traits\TextTrait;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class ValidateGateway implements ValidateGatewayInterface
{
	use TextTrait;

	public const ERROR_TYPE = 'error';
	public const INFO_TYPE = 'info';
	public const SUCCESS_TYPE = 'success';
	public const DEFAULT_ERROR_KEY = self::ERROR_TYPE . '_server';

	public const MESSAGE_TYPES = [
		self::ERROR_TYPE,
		self::INFO_TYPE,
		self::SUCCESS_TYPE,
	];

	protected $notice = [
		'type' => self::ERROR_TYPE,
		'message' => self::DEFAULT_ERROR_KEY,
	];

	protected $data = [];

	/**
	 * @var ValidateDataInterface
	 */
	private $validator;

	public function __construct()
	{
		$this->setLanguage(Breeze::NAME);
	}

	public function setValidator(ValidateDataInterface $validator): bool
	{
		$this->validator = $validator;

		$this->validator->setData($this->data);

		return true;
	}

	public function isValid(): bool
	{
		foreach ($this->validator->getSteps() as $step)
		{
			if (!method_exists($this->validator, $step))
				continue;

			try {
				$this->validator->{$step}();
				$this->data = $this->validator->getData();
			} catch (InvalidStatusException $e) {
				$this->setNotice([
					'message' => sprintf(
						$this->getText(self::DEFAULT_ERROR_KEY),
						$this->getText($e->getMessage())
					),
				]);

				return false;
			}
			catch (InvalidCommentException $e) {
				$this->setNotice([
					'message' => sprintf(
						$this->getText(self::DEFAULT_ERROR_KEY),
						$this->getText($e->getMessage())
					),
				]);

				return false;
			}
			catch (ValidateDataException $e) {
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
			'type' => self::SUCCESS_TYPE,
			'message' => $this->getText(self::INFO_TYPE . '_' . $this->validator->successKeyString())
		]);

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
