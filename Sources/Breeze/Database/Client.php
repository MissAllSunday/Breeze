<?php

class Client implements ClientInterface
{
	protected $db;

	public function __construct()
	{
		global $smcFunc;

		$this->db = $smcFunc;
	}

	public function fetch(): array
	{
		// TODO: Implement fetch() method.
	}

	public function insert(): int
	{
		// TODO: Implement insert() method.
	}

	public function update(): int
	{
		// TODO: Implement update() method.
	}

	public function delete(): int
	{
		// TODO: Implement delete() method.
	}
}