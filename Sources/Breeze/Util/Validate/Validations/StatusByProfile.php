<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations;

use Breeze\Entity\StatusEntity;

class StatusByProfile extends ValidateData implements ValidateDataInterface
{
	public array $steps = [
		'clean',
		'isInt',
		'areValidUsers',
		'ignoreList' // TODO add validation for current user on ignoreList
	];

	protected const PARAMS = [
		StatusEntity::COLUMN_OWNER_ID => 0,
	];

	protected const SUCCESS_KEY = '';

	public function getParams(): array
	{
		return self::PARAMS;
	}

	public function getInts(): array
	{
		return [
			StatusEntity::COLUMN_OWNER_ID
		];
	}

	public function getStrings(): array
	{
		return [];
	}

	public function getUserIdsNames(): array
	{
		return [
			StatusEntity::COLUMN_OWNER_ID
		];
	}

	public function getPosterId(): int
	{
		return $this->data[StatusEntity::COLUMN_OWNER_ID] ?? 0;
	}

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}
}
