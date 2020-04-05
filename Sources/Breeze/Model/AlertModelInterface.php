<?php

declare(strict_types=1);

namespace Breeze\Model;

interface AlertModelInterface extends  BaseModelInterface
{
	public function getAlertById(int $alertId): array;

	public function checkAlert(int $userId, string $alertType, int $alertId = 0, string $alertSender = ''): bool;
}
