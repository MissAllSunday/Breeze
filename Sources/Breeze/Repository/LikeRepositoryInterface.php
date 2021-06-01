<?php

declare(strict_types=1);


namespace Breeze\Repository;

interface LikeRepositoryInterface
{
	public function getByContent(string $type, int $contentId): array;
}
