<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

class ValidateComment extends ValidateData implements ValidateDataInterface
{
	protected const PARAM_POSTER_ID = 'posterId';
	protected const PARAM_STATUS_OWNER_ID = 'statusOwnerId';
	protected const PARAM_PROFILE_OWNER_IF = 'profileOwnerId';
	protected const PARAM_STATUS_ID = 'statusId';
	protected const PARAM_BODY = 'body';

	protected const PARAMS = [
		self::PARAM_POSTER_ID,
		self::PARAM_STATUS_OWNER_ID,
		self::PARAM_PROFILE_OWNER_IF,
		self::PARAM_STATUS_ID,
		self::PARAM_BODY,
	];

	public function getInts(): array
	{
		return [
			self::PARAM_POSTER_ID,
			self::PARAM_STATUS_OWNER_ID,
			self::PARAM_PROFILE_OWNER_IF,
			self::PARAM_STATUS_ID,
		];
	}

	public function getStrings(): array
	{
		return [self::PARAM_BODY];
	}

	public function getParams(): array
	{
		return self::PARAMS;
	}
}
