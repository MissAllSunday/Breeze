<?php

declare(strict_types=1);

namespace Breeze\Service;

interface NotificationServiceInterface
{
	public function create(array $data): void;
}
