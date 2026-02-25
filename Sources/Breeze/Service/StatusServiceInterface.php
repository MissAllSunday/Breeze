<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\EmptyDataException;

interface StatusServiceInterface
{
	/**
	 * @throws EmptyDataException
	 * @return array [StatusEntity]
	 *
	 */
	public function getByProfile(int $wallId, ?string $cursor = null): array;

	public function getRepository(): StatusRepositoryInterface;

	/**
	 * @throws EmptyDataException
	 * @return array [StatusEntity]
	 *
	 */
	public function getByBuddies(?string $cursor = null): array;

	/**
	 * @throws EmptyDataException
	 * @throws DataNotFoundException
	 */
	public function getById(int $statusId): array;

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

	public function recountComments(): void;

	public function recountLikes(): void;
}
