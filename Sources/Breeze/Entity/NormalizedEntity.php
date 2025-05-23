<?php

declare(strict_types=1);


namespace Breeze\Entity;

abstract class NormalizedEntity extends Entity implements EntityInterface
{
	abstract public function getColumnMap(): array;

	public function setEntity(array $entry): Entity
	{
		foreach ($this->castValues($this->normalizeKeys($entry)) as $key => $value) {
			$setCall = 'set' . ucfirst($this->snakeToCamel($key));
			$this->{$setCall}($value);
		}

		return $this;
	}

	public function normalizeKeys(array $rawRowKeys = []): array {
		$columnMap = $this->getColumnMap();
		array_walk($rawRowKeys, function ($value, &$key) use ($columnMap): void {
			$key = $columnMap[$key];
		});

		return $rawRowKeys;
	}
}
