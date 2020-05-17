<?php

declare(strict_types=1);

namespace Breeze\Util\Validate;

use Breeze\Util\Permissions;

class ValidateComment extends ValidateData implements ValidateDataInterface
{
	public const PARAM_POSTER_ID = 'posterId';
	public const PARAM_STATUS_OWNER_ID = 'statusOwnerId';
	public const PARAM_PROFILE_OWNER_ID = 'profileOwnerId';
	public const PARAM_STATUS_ID = 'statusId';
	public const PARAM_BODY = 'body';

	protected const PARAMS = [
		self::PARAM_POSTER_ID => 0,
		self::PARAM_STATUS_OWNER_ID => 0,
		self::PARAM_PROFILE_OWNER_ID => 0,
		self::PARAM_STATUS_ID => 0,
		self::PARAM_BODY => '',
	];

	public function getSteps(): array
	{
		$steps = self::STEPS;
		$steps[] = 'permissions';

		return $steps;
	}

	public function permissions(): void
	{
		if (!Permissions::isAllowedTo(Permissions::POST_COMMENTS))
			throw new ValidateDataException('postComments');
	}

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
