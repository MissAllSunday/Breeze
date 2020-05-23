<?php

declare(strict_types=1);


namespace Breeze\Service;

interface CommentServiceInterface extends BaseServiceInterface
{
	public function saveAndGet(array $data): array;
}
