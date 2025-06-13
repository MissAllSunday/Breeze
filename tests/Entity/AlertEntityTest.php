<?php

declare(strict_types=1);


namespace Breeze\Entity;

use DateTimeImmutable;
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

	/**
	 * @throws \DateMalformedStringException
	 */
	public function testSettersAndGetters(): void
	{
		$entity = new AlertEntity();
		$entity->setIdAlert(1);
		$this->assertEquals(1, $entity->getIdAlert());
		$entity->setAlertTime(time());
		$this->assertInstanceOf(DateTimeImmutable::class, $entity->getAlertTime());
		$entity->setIdMember(1);
		$this->assertEquals(1, $entity->getIdMember());
		$entity->setIdMemberStarted(2);
		$this->assertEquals(2, $entity->getIdMemberStarted());
		$entity->setMemberName('test');
		$this->assertEquals('test', $entity->getMemberName());
		$entity->setContentType('test');
		$this->assertEquals('test', $entity->getContentType());
		$entity->setContentId(1);
		$this->assertEquals(1, $entity->getContentId());
		$entity->setContentAction('test');
		$this->assertEquals('test', $entity->getContentAction());
		$entity->setIsRead(true);
		$this->assertTrue($entity->isRead());
		$entity->setExtra(['test' => 'test']);
		$this->assertEquals(['test' => 'test'], $entity->getExtra());
	}
}
