<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\StatusEntity;
use Breeze\Util\Validate\EmptyDataException;

interface StatusRepositoryInterface extends BaseRepositoryInterface
{
	/**
	 * @throws InvalidStatusException
	 * @return array [StatusEntity]
	 */
	public function insert(StatusEntity $statusEntity): array;

	/**
	 * @throws EmptyDataException
	 */
	public function getByProfile(array $userProfiles = [], int $start = 0, int $maxIndex = 0): array;

	public function getBy(string $columnName, array $data = [], int $start = 0, int $maxIndex = 0): array;

	public function getById(int $id = 0): StatusEntity;

	/**
	 * @throws InvalidStatusException
	 */
	public function deleteById(int $statusId): bool;

	public function getCount(array $queryParams = []): int;

	public function getBasicInfoById(int $id): StatusEntity;
}
