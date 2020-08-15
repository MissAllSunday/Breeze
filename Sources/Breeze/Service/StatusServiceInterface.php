<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\InvalidStatusException;

interface StatusServiceInterface extends BaseServiceInterface
{
	/**
	 * @throws InvalidStatusException
	 */
	public function getByProfile(int $profileOwnerId = 0, int $start = 0): array;

	/**
	 * @throws InvalidStatusException
	 */
	public function getById(int $statusId): array;

	public function saveAndGet(array $data): array;
}
