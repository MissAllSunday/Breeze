<?php

declare(strict_types=1);

namespace Breeze\Repository;

interface NotificationRepositoryInterface
{
	public function save(array $data): int;
}
