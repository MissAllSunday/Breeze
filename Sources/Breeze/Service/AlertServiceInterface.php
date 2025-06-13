<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\AlertEntity;

interface AlertServiceInterface
{
	public function send(AlertEntity $alertEntity): void;

	public function getById(int $alertId): AlertEntity;

	public function delete(int $alertId): bool;

	public function loadUsersInfo(array $userIds = []): array;

	public function handle(array &$alerts): void;
}
