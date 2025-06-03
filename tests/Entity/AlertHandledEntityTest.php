<?php

declare(strict_types=1);

namespace Breeze\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class AlertHandledEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'sender_id',
			'sender_name',
			'sender_email',
			'sender_avatar',
			'sender_filename',
			'time',
			'visible',
			'show_links',
			'icon',
			'target_href',
			'text',
		], AlertHandledEntity::getColumns());
	}

	/**
	 * @throws \DateMalformedStringException
	 */
	public function testSettersAndGetters(): void
	{
		$entity = new AlertHandledEntity();

		$entity->setSenderId(123);
		$this->assertEquals(123, $entity->getSenderId());

		$entity->setSenderName('TestUser');
		$this->assertEquals('TestUser', $entity->getSenderName());

		$entity->setSenderEmail('test@example.com');
		$this->assertEquals('test@example.com', $entity->getSenderEmail());

		$entity->setSenderAvatar('avatar.jpg');
		$this->assertEquals('avatar.jpg', $entity->getSenderAvatar());

		$entity->setSenderFilename('filename.jpg');
		$this->assertEquals('filename.jpg', $entity->getSenderFilename());

		$entity->setSenderFilename(null);
		$this->assertNull($entity->getSenderFilename());

		$timestamp = time();
		$entity->setTime($timestamp);
		$this->assertInstanceOf(DateTimeImmutable::class, $entity->getTime());
		$this->assertEquals($timestamp, $entity->getTime()->getTimestamp());

		$dateTime = new DateTimeImmutable();
		$entity->setTime($dateTime);
		$this->assertSame($dateTime, $entity->getTime());

		$entity->setVisible(true);
		$this->assertTrue($entity->isVisible());

		$entity->setVisible(false);
		$this->assertFalse($entity->isVisible());

		$entity->setShowLinks(true);
		$this->assertTrue($entity->isShowLinks());

		$entity->setShowLinks(false);
		$this->assertFalse($entity->isShowLinks());

		$entity->setIcon('icon-class');
		$this->assertEquals('icon-class', $entity->getIcon());

		$entity->setTargetHref('/path/to/target');
		$this->assertEquals('/path/to/target', $entity->getTargetHref());

		$entity->setText('Alert message text');
		$this->assertEquals('Alert message text', $entity->getText());
	}
}
