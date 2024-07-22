<?php

declare(strict_types=1);


namespace Breeze\Entity;

abstract class NormalizeEntity extends BaseEntity implements BaseEntityInterface
{
	abstract public function getColumnMap(): array;

	public function setEntry(array $entry): void
	{
		foreach ($this->normalizeKeys($entry) as $key => $value) {
			$setCall = 'set' . $this->snakeToCamel($key);
			$this->{$setCall}($value);
		}
	}

	public function normalizeKeys(array $rawRowKeys = []): array {
		$columnMap = $this->getColumnMap();
		array_walk($rawRowKeys, function ($value, &$key) use ($columnMap): void {
			$key = $columnMap[$key];
		});

		return $rawRowKeys;
	}
}
