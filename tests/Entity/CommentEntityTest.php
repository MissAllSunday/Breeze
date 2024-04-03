<?php

declare(strict_types=1);


namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class CommentEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'id',
			'statusId',
			'userId',
			'createdAt',
			'body',
			'likes',
		], CommentEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('breeze_comments', CommentEntity::getTableName());
	}
}
