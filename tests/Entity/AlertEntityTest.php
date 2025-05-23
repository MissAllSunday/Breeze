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

	public function testGetColumnMap(): void
	{
		$entity = new AlertEntity();
		$this->assertEquals(AlertEntity::KEY_MAP, $entity->getColumnMap());
	}

	public function testSettersAndGetters(): void
	{
		$entity = new AlertEntity();

		$entity->setId(123);
		$this->assertEquals(123, $entity->getId());

		$entity->setTime('2023-01-01');
		$this->assertEquals('2023-01-01', $entity->getTime());

		$entity->setIdMember(456);
		$this->assertEquals(456, $entity->getIdMember());

		$entity->setIdMemberStarted(789);
		$this->assertEquals(789, $entity->getIdMemberStarted());

		$entity->setMemberName('TestUser');
		$this->assertEquals('TestUser', $entity->getMemberName());

		$entity->setType('notification');
		$this->assertEquals('notification', $entity->getType());

		$entity->setContentId(101);
		$this->assertEquals(101, $entity->getContentId());

		$entity->setAction('post');
		$this->assertEquals('post', $entity->getAction());

		$entity->setIsRead(true);
		$this->assertTrue($entity->isRead());

		// Test boolean conversion for setIsRead
		$entity->setIsRead(1);
		$this->assertTrue($entity->isRead());

		$entity->setExtra('{"key":"value"}');
		$this->assertEquals('{"key":"value"}', $entity->getExtra());
	}
}
