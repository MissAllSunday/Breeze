<?php

declare(strict_types=1);


namespace Breeze\Repository;

interface StatusRepositoryInterface
{
	/**
	 * @throws InvalidStatusException
	 */
	public function save(array $data): int;

	/**
	 * @throws InvalidStatusException
	 */
	 public function getByProfile(int $profileOwnerId = 0, int $start = 0): array;

	/**
	 * @throws InvalidStatusException
	 */
	 public function getById(int $statusId = 0): array;
}
