<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Util\Validate\EmptyDataException;

interface StatusServiceInterface
{
	/**
	 * @throws EmptyDataException
	 * @return array [StatusEntity]
	 *
	 */
	public function getByProfile(int $wallId, int $start): array;

	/**
	 * @throws EmptyDataException
	 * @return array [StatusEntity]
	 *
	 */
	public function getByBuddies(int $start): array;

	/**
	 * @throws InvalidStatusException
	 */
	public function deleteById(int $statusId): void;

	/**
	 * @throws InvalidStatusException
	 * @return array [StatusEntity]
	 */
	public function save(array $data): array;

	public function currentUserInfo(): array;

	public function getCount(string $columnName, array $ids = []): int;
}
