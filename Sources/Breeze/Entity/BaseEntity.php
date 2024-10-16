<?php

declare(strict_types=1);


namespace Breeze\Entity;

abstract class BaseEntity
{
	public const ALIAS_ID = '%1$s.%2$s AS %2$s';
	public const WRONG_VALUES = 'error_wrong_values';

	abstract public static function getTableName(): string;

	abstract public static function getColumns(): array;

	public function __construct(array $entry = [])
	{
		$this->setEntry($entry);
	}

	public function setEntry(array $entry): void
	{
		foreach ($this->getColumns() as $key => $value) {
			$setCall = 'set' . $this->snakeToCamel($key);
			$this->{$setCall}($value);
		}
	}

	protected function snakeToCamel($input): string
	{
		return \lcfirst(\str_replace('_', '', \ucwords($input, '_')));
	}
}
