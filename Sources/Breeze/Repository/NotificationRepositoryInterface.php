<?php

namespace Breeze\Repository;

interface NotificationRepositoryInterface
{
	public function save(array $data): int;
}
