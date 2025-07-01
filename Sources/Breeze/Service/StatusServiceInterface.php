<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\StatusHandledEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Util\Validate\EmptyDataException;

interface StatusServiceInterface
{
	/**
	 * @throws EmptyDataException
	 * @return array [StatusHandledEntity]
	 *
	 */
	public function getByProfile(int $wallId, int $start): array;

	/**
	 * @throws EmptyDataException
	 * @return array [StatusHandledEntity]
	 *
	 */
	public function getByBuddies(int $start): array;

	/**
	 * @throws InvalidStatusException
	 */
	public function deleteById(int $statusId): void;

	/**
	 * @throws InvalidStatusException
	 * @return array [StatusHandledEntity]
	 */
	public function save(array $data): array;

	public function currentUserInfo(): array;

	public function getCount(string $columnName, array $ids = []): int;
}
