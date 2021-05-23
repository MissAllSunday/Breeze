<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Likes;

use Breeze\Entity\LikeEntity;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class Like extends ValidateLikes implements ValidateDataInterface
{
	protected const PARAMS = [
		LikeEntity::PARAM_LIKE => 0,
		LikeEntity::PARAM_SA => '',
	];

	protected const DEFAULT_PARAMS = [
		LikeEntity::PARAM_LIKE => 0,
		LikeEntity::PARAM_SA => '',
	];

	protected const SUCCESS_KEY = 'moodCreated';

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}

	public function getSteps(): array
	{
		return array_merge($this->steps, [
			self::INT,
			self::STRING,
		]);
	}

	/**
	 * @throws ValidateDataException
	 */
	public function permissions(): void
	{
		if (!Permissions::isAllowedTo(Permissions::ADMIN_FORUM)) {
			throw new ValidateDataException('moodCreated');
		}
	}

	public function getInts(): array
	{
		return [
			LikeEntity::PARAM_LIKE,
		];
	}

	public function getStrings(): array
	{
		return [
			LikeEntity::PARAM_SA,
		];
	}

	public function getPosterId(): int
	{
		return 0;
	}

	public function getParams(): array
	{
		return $this->data;
	}

	public function getData(): array
	{
		return $this->getParams();
	}

	public function getUserIdsNames(): array
	{
		return [];
	}
}
