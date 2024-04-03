<?php

declare(strict_types=1);


namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class MentionEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'id' => 'id',
			'owner_id' => 'owner_id',
			'poster_id' => 'poster_id',
			'time' => 'time',
			'body' => 'body',
		], MentionEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('mentions', MentionEntity::getTableName());
	}
}
