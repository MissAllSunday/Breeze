<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\DataNotFoundException;

class PostStatus extends ValidateStatus
{
	protected const PARAMS = [
		StatusEntity::WALL_ID => 0,
		StatusEntity::USER_ID => 0,
		StatusEntity::BODY => '',
	];

	protected const SUCCESS_KEY = 'published_status';

	public function __construct(protected StatusRepositoryInterface $statusRepository)
	{
	}

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}

	public function getSteps(): array
	{
		return array_merge(self::DEFAULT_STEPS, [
			self::VALID_USERS,
			self::FLOOD_CONTROL,
		]);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function permissions(): void
	{
		if (!Permissions::isAllowedTo(Permissions::POST_STATUS)) {
			throw new DataNotFoundException('postStatus');
		}
	}

	public function getInts(): array
	{
		return [
			StatusEntity::WALL_ID,
			StatusEntity::USER_ID,
		];
	}

	public function getUserIdsNames(): array
	{
		return [
			StatusEntity::WALL_ID,
			StatusEntity::USER_ID,
		];
	}

	public function getStrings(): array
	{
		return [StatusEntity::BODY];
	}

	public function getPosterId(): int
	{
		return $this->data[StatusEntity::USER_ID] ?? 0;
	}

	public function getParams(): array
	{
		return self::PARAMS;
	}
}
