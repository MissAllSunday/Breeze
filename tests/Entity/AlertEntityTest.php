<?php

declare(strict_types=1);


namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class AlertEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'id_alert',
			'alert_time',
			'id_member',
			'id_member_started',
			'member_name',
			'content_type',
			'content_id',
			'content_action',
			'is_read',
			'extra',
		], AlertEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('user_alerts', AlertEntity::getTableName());
	}
}
