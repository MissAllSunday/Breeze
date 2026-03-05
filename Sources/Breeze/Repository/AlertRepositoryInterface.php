<?php

declare(strict_types=1);

namespace Breeze\Repository;

use Breeze\Entity\AlertEntity as AlertEntity;

interface AlertRepositoryInterface extends BaseRepositoryInterface
{
	public function insert(AlertEntity $alertEntity): int;

	public function update(AlertEntity $alertEntity): AlertEntity;

	public function checkAlert(AlertEntity $alertEntity): bool;

	public function delete(array $alertIds): bool;
}
