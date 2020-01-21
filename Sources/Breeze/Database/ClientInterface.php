<?php

interface ClientInterface
{
	public function fetch(): array;

	public function insert(): int;

	public function update(): int;

	public function delete(): int;
}