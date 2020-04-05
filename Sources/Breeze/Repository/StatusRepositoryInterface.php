<?php

declare(strict_types=1);


namespace Breeze\Repository;

interface StatusRepositoryInterface
{
	 public function getStatusByProfile(int $profileOwnerId = 0): void;
}
