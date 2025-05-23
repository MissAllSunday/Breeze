<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\AlertEntity;

interface AlertServiceInterface
{
	public function create(AlertEntity $alertEntity): int;

	public function getById(int $alertId): array;

	public function delete(int $alertId): bool;
}
