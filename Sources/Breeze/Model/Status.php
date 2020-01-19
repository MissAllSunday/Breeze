<?php

declare(strict_types=1);


use Breeze\Entity\Status as StatusEntity;

class Status extends Base
{
	function getCount(): int
	{
	}

	function insert(): bool
	{
		// TODO: Implement insert() method.
	}

	function update(int $id): array
	{
		// TODO: Implement update() method.
	}


	function getSingleValue(int $id): array
	{

	}

	function getById(int $id): array
	{

	}

	function generateData($row): array
	{
		// TODO: Implement generateData() method.
	}

	function setEntity(): void
	{
		$this->entity = new StatusEntity();
	}

	function getEntity(): StatusEntity
	{
		return $this->entity;
	}

	function getTableName(): string
	{
		// TODO: Implement getTableName() method.
	}

	function getColumnId(): string
	{
		// TODO: Implement getColumnId() method.
	}

	function getColumns(): array
	{
		// TODO: Implement getColumns() method.
	}
}