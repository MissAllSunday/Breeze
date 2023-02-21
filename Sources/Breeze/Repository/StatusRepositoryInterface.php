<?php

declare(strict_types=1);


namespace Breeze\Repository;

interface StatusRepositoryInterface extends BaseRepositoryInterface
{
	/**
	 * @throws InvalidStatusException
	 */
	public function save(array $data): int;

	/**
	 * @throws InvalidStatusException
	 */
	public function getByProfile(array $userProfiles = [], int $start = 0): array;

	/**
	 * @throws InvalidStatusException
	 */
	public function getById(int $statusId = 0): array;

	/**
	 * @throws InvalidStatusException
	 */
	public function deleteById(int $statusId): bool;
}
