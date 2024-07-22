<?php

declare(strict_types=1);


namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class MentionEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'content_id',
			'content_type',
			'id_mentioned',
			'id_member',
			'time',
		], MentionEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('mentions', MentionEntity::getTableName());
	}
}
