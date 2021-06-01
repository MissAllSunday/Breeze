<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Likes;

use Breeze\Entity\LikeEntity;
use Breeze\Repository\InvalidLikeException;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class Like extends ValidateLikes implements ValidateDataInterface
{
	protected const CHECK_TYPE = 'checkType';

	protected const VALIDATE = 'validate';

	protected const PARAMS = [
		LikeEntity::ID => 0,
		LikeEntity::TYPE => '',
		LikeEntity::PARAM_SA => '',
	];

	protected const DEFAULT_PARAMS = [
		LikeEntity::ID => 0,
		LikeEntity::TYPE => '',
		LikeEntity::PARAM_SA => '',
	];

	protected const SUCCESS_KEY = 'likeSuccess';

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
			self::VALIDATE,
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

	/**
	 * @throws InvalidLikeException
	 */
	public function validate(): void
	{
		$type =  $this->data[LikeEntity::TYPE];
		$contentId =  $this->data[LikeEntity::ID];

		$this->likeService->getByContent($type, $contentId);
	}

	public function getInts(): array
	{
		return [
			LikeEntity::ID,
		];
	}

	public function getStrings(): array
	{
		return [
			LikeEntity::PARAM_SA,
			LikeEntity::TYPE,
		];
	}

	public function getPosterId(): int
	{
		return 0;
	}

	public function getParams(): array
	{
		return self::DEFAULT_PARAMS;
	}

	public function getData(): array
	{
		return $this->data;
	}

	public function getUserIdsNames(): array
	{
		return [];
	}
}
