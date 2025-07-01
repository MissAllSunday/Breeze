<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\StatusEntity;
use Breeze\Entity\StatusHandledEntity;
use Breeze\Util\Validate\EmptyDataException;

interface StatusRepositoryInterface extends BaseRepositoryInterface
{
	/**
	 * @return array [StatusHandledEntity]
	 * @throws InvalidStatusException
	 */
	public function insert(StatusEntity $statusEntity): array;

	/**
	 * @throws EmptyDataException
	 */
	public function getByProfile(array $userProfiles = [], int $start = 0, int $maxIndex = 0): array;

	public function getById(int $id = 0): StatusHandledEntity;

	/**
	 * @throws InvalidStatusException
	 */
	public function deleteById(int $statusId): bool;

	public function getCount(array $queryParams = []): int;
}
