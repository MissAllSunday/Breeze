<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Util\Validate\EmptyDataException;

interface StatusRepositoryInterface extends BaseRepositoryInterface
{
	/**
	 * @throws InvalidStatusException
	 */
	public function save(array $data): int;

	/**
	 * @throws EmptyDataException
	 */
	public function getByProfile(array $userProfiles = [], int $start = 0, int $maxIndex = 0): array;

	/**
	 * @throws InvalidStatusException
	 */
	public function getById(int $statusId = 0): array;

	/**
	 * @throws InvalidStatusException
	 */
	public function deleteById(int $statusId): bool;
}
