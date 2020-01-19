<?php


abstract class Base
{
	abstract function insert(): bool;
	abstract function delete(): bool;
	abstract function update(): array;
	abstract function getSingleValue(int $id): array;
	abstract function getLastValue(): array;
	abstract function getById(int $id): array;
	abstract function getCount(): int;
}