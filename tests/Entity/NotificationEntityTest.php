<?php

declare(strict_types=1);


namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class NotificationEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'background_tasks',
			'id_task',
			'task_file',
			'task_class',
			'task_data',
			'claimed_time',
		], NotificationEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('background_tasks', NotificationEntity::getTableName());
	}
}
