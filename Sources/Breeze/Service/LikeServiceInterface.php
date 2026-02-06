<?php

declare(strict_types=1);

namespace Breeze\Service;

interface LikeServiceInterface
{
	public function countOrphans(): int;

	public function deleteOrphans(): void;
}
