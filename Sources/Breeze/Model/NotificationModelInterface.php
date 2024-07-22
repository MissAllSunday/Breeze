<?php

declare(strict_types=1);

namespace Breeze\Model;

interface NotificationModelInterface extends BaseModelInterface
{
	public function insert(array $data, int $id = 0): int;

	public function getById(int $statusId): array;
}
