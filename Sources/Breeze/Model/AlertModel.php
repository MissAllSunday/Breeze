<?php

declare(strict_types=1);

namespace Breeze\Model;

use Breeze\Entity\AlertEntity as AlertEntity;

class AlertModel
{
	public function getTableName(): string
	{
		return AlertEntity::TABLE;
	}

	public function getColumnId(): string
	{
		return AlertEntity::COLUMN_ID;
	}

	public function getColumns(): array
	{
		return AlertEntity::getColumns();
	}
}
