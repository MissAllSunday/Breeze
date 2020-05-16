<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

class ValidateComment extends ValidateData implements ValidateDataInterface
{
	protected const PARAM_POSTER_ID = 'posterId';
	protected const PARAM_STATUS_OWNER_ID = 'statusOwnerId';
	protected const PARAM_PROFILE_OWNER_ID = 'profileOwnerId';
	protected const PARAM_STATUS_ID = 'statusId';
	protected const PARAM_BODY = 'body';

	protected const PARAMS = [
		self::PARAM_POSTER_ID => 0,
		self::PARAM_STATUS_OWNER_ID => 0,
		self::PARAM_PROFILE_OWNER_ID => 0,
		self::PARAM_STATUS_ID => 0,
		self::PARAM_BODY => '',
	];

	public function getInts(): array
	{
		return [
			self::PARAM_POSTER_ID,
			self::PARAM_STATUS_OWNER_ID,
			self::PARAM_PROFILE_OWNER_ID,
			self::PARAM_STATUS_ID,
		];
	}

	public function getUserIdsNames(): array
	{
		return [
			self::PARAM_POSTER_ID,
			self::PARAM_STATUS_OWNER_ID,
			self::PARAM_PROFILE_OWNER_ID,
		];
	}

	public function getStrings(): array
	{
		return [self::PARAM_BODY];
	}

	public function getPosterId(): int
	{
		return $this->data[self::PARAM_POSTER_ID] ?? 0;
	}

	public function getParams(): array
	{
		return self::PARAMS;
	}
}
