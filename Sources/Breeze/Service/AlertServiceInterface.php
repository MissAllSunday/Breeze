<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\AlertEntity;
use Breeze\Entity\EntityInterface;

interface AlertServiceInterface
{
	public function send(AlertEntity $alertEntity): void;

	public function getById(int $alertId): AlertEntity|EntityInterface;

	public function delete(int $alertId): bool;

	public function loadUsersInfo(array $userIds = []): array;

	public function handle(array &$alerts): void;
}
