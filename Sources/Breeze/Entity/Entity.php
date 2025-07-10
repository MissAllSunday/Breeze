<?php

declare(strict_types=1);


namespace Breeze\Entity;

abstract class Entity implements EntityInterface
{
	public const string ID = 'id';
	public const string ALIAS_ID = '%1$s.%2$s AS %2$s';
	public const string WRONG_VALUES = 'error_wrong_values';

	abstract public static function getTableName(): string;

	abstract public static function getColumns(): array;

	public function __construct(array $entry = [])
	{
		$this->setEntity($entry);
	}

	public function setEntity(array $entry): Entity
	{
		foreach ($entry as $key => $value) {
			$setCall = 'set' . ucfirst($this->snakeToCamel($key));
			$this->{$setCall}($this->castValue($key, $value));
		}

		return $this;
	}

	public function snakeToCamel(string $input): string
	{
		return \lcfirst(\str_replace('_', '', \ucwords($input, '_')));
	}

	protected function castValues(array $data) : array
	{
		return array_map(function ($column) {
			return is_numeric($column) ? ((int) $column) : $column;
		}, $data);
	}

	public function toArray(): array
	{
		return get_object_vars($this);
	}
}
