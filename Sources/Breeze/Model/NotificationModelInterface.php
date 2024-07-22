<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\NotificationEntity as NotificationEntity;

interface NotificationModelInterface extends BaseModelInterface
{
	public function insert(array $data, int $id = 0): int;

	public function getById(int $statusId): NotificationEntity;
}
