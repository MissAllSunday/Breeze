<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateData;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\ValidateDataInterface;

class PostComment extends ValidateData implements ValidateDataInterface
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

	protected const SUCCESS_KEY = 'published_comment';

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}

	public function getSteps(): array
	{
		$this->steps = self::ALL_STEPS;
		$this->steps[] = 'permissions';

		return $this->steps;
	}

	public function setSteps(array $customSteps): void
	{
		$this->steps = $customSteps;
	}

	/**
	 * @throws ValidateDataException
	 */
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
