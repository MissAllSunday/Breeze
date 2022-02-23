<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class StatusByProfile extends ValidateStatus implements ValidateDataInterface
{
	public array $steps = [
		self::CLEAN,
		self::INT,
		self::VALID_USERS,
		self::IGNORE_LIST, // TODO add validation for current user on ignoreList
	];

	protected const PARAMS = [
		StatusEntity::WALL_ID => 0,
	];

	protected const SUCCESS_KEY = '';

	public function getParams(): array
	{
		return array_merge(self::PARAMS, $this->data);
	}

	public function getInts(): array
	{
		return [
			StatusEntity::WALL_ID,
		];
	}

	public function getStrings(): array
	{
		return [];
	}

	public function getUserIdsNames(): array
	{
		return [
			StatusEntity::WALL_ID,
		];
	}

	public function getPosterId(): int
	{
		return $this->data[StatusEntity::WALL_ID] ?? 0;
	}

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}
}
