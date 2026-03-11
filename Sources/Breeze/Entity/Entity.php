<?php

declare(strict_types=1);


namespace Breeze\Entity;

abstract class Entity implements EntityInterface
{
	public const string ALIAS_ID = '%1$s.%2$s AS %2$s';
	public const string WRONG_VALUES = 'error_wrong_values';

	abstract public static function getTableName(): string;

	abstract public static function getColumns(): array;

	abstract public function castValue(string $columnName, mixed $value): mixed;

	abstract public static function from(array $data = []): EntityInterface;

	protected function __construct(array $entry = [])
	{
		$this->setEntity($entry);
	}

	public function setEntity(array $entry): Entity
	{
		foreach ($entry as $key => $value) {
			$setCall = 'set' . ucfirst($this->snakeToCamel($key));
			if (method_exists($this, $setCall)) {
				$this->{$setCall}($this->castValue($key, $value));
			}
		}

		return $this;
	}

	public function snakeToCamel(string $input): string
	{
		return \lcfirst(\str_replace('_', '', \ucwords($input, '_')));
	}

	public function toInsert(): array
	{
		return array_intersect_key($this->toArray(), array_flip(static::getColumns()));
	}

	public function toArray(): array
	{
		$data = get_object_vars($this);

		foreach ($data as $key => $value) {
			if ($value instanceof \DateTimeInterface) {
				$data[$key] = $value->getTimestamp();
			}

			if (is_bool($value)) {
				$data[$key] = $value ? 1 : 0;
			}
		}

		return $data;
	}
}
