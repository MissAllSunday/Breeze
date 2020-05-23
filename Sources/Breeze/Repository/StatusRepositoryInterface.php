<?php

declare(strict_types=1);


namespace Breeze\Repository;

interface StatusRepositoryInterface
{
	 public function getByProfile(int $profileOwnerId = 0, int $start = 0): array;
}
