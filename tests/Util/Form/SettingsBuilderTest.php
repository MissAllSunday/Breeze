<?php

declare(strict_types=1);

namespace Breeze\Util\Form;

use PHPUnit\Framework\TestCase;

class SettingsBuilderTest extends TestCase
{
	private SettingsBuilder $builder;

	protected function setUp(): void
	{
		$this->builder = new SettingsBuilder();
		
		// Initialize globals
		$GLOBALS['context'] = [
			'session_var' => 'foo',
			'session_id' => 'baz',
		];
		$GLOBALS['txt'] = [];
	}

	protected function tearDown(): void
	{
		// Restore global state
		$GLOBALS['context'] = [
			'session_var' => 'foo',
			'session_id' => 'baz',
			'cust_profile_fields_placement' => [
				'standard',
				'icons',
				'above_signature',
				'below_signature',
				'below_avatar',
				'above_member',
				'bottom_poster',
				'before_member',
				'after_member',
			],
		];
	}

	public function testImplementsSettingsBuilderInterface(): void
	{
		$this->assertInstanceOf(SettingsBuilderInterface::class, $this->builder);
	}

	public function testGetFormattersReturnsArray(): void
	{
		$formatters = $this->builder->getFormatters();

		$this->assertIsArray($formatters);
	}

	public function testGetFormattersReturnsFormatterInstances(): void
	{
		$formatters = $this->builder->getFormatters();

		foreach ($formatters as $formatter) {
			$this->assertIsObject($formatter);
		}
	}

	public function testGetConfigVarsSettingsReturnsArray(): void
	{
		$configVars = $this->builder->getConfigVarsSettings();

		$this->assertIsArray($configVars);
	}

	public function testBuilderUsesTextTrait(): void
	{
		$this->assertTrue(method_exists($this->builder, 'getText'));
		$this->assertTrue(method_exists($this->builder, 'getSmfText'));
	}
}
