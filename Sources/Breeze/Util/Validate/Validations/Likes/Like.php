<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Likes;

use Breeze\Entity\LikeEntity;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class Like extends ValidateLikes implements ValidateDataInterface
{
	protected const CHECK_TYPE = 'checkType';

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
			self::PERMISSIONS,
			self::FEATURE_ENABLE,
			self::CHECK_TYPE,
		]);
	}

	/**
	 * @throws ValidateDataException
	 */
	public function permissions(): void
	{
		if (!Permissions::isAllowedTo(Permissions::LIKES_LIKE)) {
			throw new ValidateDataException('likesLike');
		}
	}

	/**
	 * @throws ValidateDataException
	 */
	public function isFeatureEnable(): void
	{
		if (!$this->modSetting('enable_likes')) {
			throw new ValidateDataException('likesNotEnabled');
		}
	}

	/**
	 * @throws ValidateDataException
	 */
	public function checkType(): void
	{
		$type =  $this->data[LikeEntity::TYPE];

		if (!in_array($type, LikeEntity::getTypes())) {
			throw new ValidateDataException('likesTypeInvalid');
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
		return array_merge(self::DEFAULT_PARAMS, $this->data);
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
