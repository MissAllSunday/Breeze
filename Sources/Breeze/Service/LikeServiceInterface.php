<?php

declare(strict_types=1);


namespace Breeze\Service;

interface LikeServiceInterface extends BaseServiceInterface
{
	public function getByContent(string $type, int $contentId): array;
}
