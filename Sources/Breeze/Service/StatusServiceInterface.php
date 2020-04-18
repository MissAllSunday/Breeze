<?php

declare(strict_types=1);


namespace Breeze\Service;

interface StatusServiceInterface extends BaseServiceInterface
{
	public function getByProfile(int $profileOwnerId = 0, int $start = 0): array;
}
