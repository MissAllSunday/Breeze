<?php

declare(strict_types=1);

namespace Breeze\Service;

use Breeze\Entity\AlertEntity;
use Breeze\Repository\AlertRepositoryInterface;

class AlertService implements AlertServiceInterface
{
	public function __construct(
		protected AlertRepositoryInterface $alertRepository
	) {}

	public function create(AlertEntity $alertEntity): int
	{
		return $this->alertRepository->insert($alertEntity);
	}

	public function getById(int $alertId): array
	{
		return $this->alertRepository->getById($alertId);
	}

	public function delete(int $alertId): bool
	{
		return $this->alertRepository->delete([$alertId]);
	}
}
