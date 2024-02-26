<?php

declare(strict_types=1);


namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class SettingsEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'master' => 'check',
			'forceWall' => 'check',
			'maxBuddiesNumber' => 'int',
			'aboutMeMaxLength' => 'int',
			'maxFloodNum' => 'int',
			'maxFloodMinutes' => 'int',
		], SettingsEntity::getColumns());
	}

	public function testDefaultValues(): void
	{
		$this->assertEquals([
			'check' => false,
			'int' => 0,
			'text' => '',
			'textArea' => '',
			'select' => [],
		], SettingsEntity::defaultValues());
	}
}
