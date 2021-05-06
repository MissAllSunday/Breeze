<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Breeze\Traits\SettingsTrait as SettingsTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SettingsTest extends TestCase
{
	/**
	 * @var MockObject&SettingsTrait
	 */
	private $settingsTrait;

	protected function setUp(): void
	{
		$this->settingsTrait = $this->createSettingsTraitMock();
	}

	/**
	 * @return  MockObject&SettingsTrait
	 */
	protected function createSettingsTraitMock()
	{
		return $this->getMockForTrait(SettingsTrait::class);
	}

	/**
	 * @dataProvider getSettingProvider
	 */
	public function testGetSetting(string $settingName, $fallBack, $expected): void
	{
		$setting = $this->settingsTrait->getSetting($settingName, $fallBack);

		$this->assertEquals($expected, $setting);
	}

	public function getSettingProvider(): array
	{
		return [
			'string exists' =>
			[
				'settingName' => 'someSetting',
				'fallback' => false,
				'expected' => 666,
			],
			'use fallback' =>
			[
				'settingName' => 'nope',
				'fallback' => 'Luffy',
				'expected' => 'Luffy',
			],
			'empty setting name' =>
			[
				'settingName' => '',
				'fallback' => 'Nami',
				'expected' => 'Nami',
			],
		];
	}

	/**
	 * @dataProvider enableProvider
	 */
	public function testEnable(string $settingName, bool $expected): void
	{
		$enable = $this->settingsTrait->isEnable($settingName);

		$this->assertIsBool($expected);
		$this->assertEquals($expected, $enable);
	}

	public function enableProvider(): array
	{
		return [
			'setting enable' =>
			[
				'settingName' => 'master',
				'expected' => true,
			],
			'setting disabled' =>
			[
				'settingName' => 'time_machine',
				'expected' => false,
			],
		];
	}

	/**
	 * @dataProvider modSettingProvider
	 */
	public function testModSetting(string $settingName, $fallBack, $expected): void
	{
		$modSetting = $this->settingsTrait->modSetting($settingName, $fallBack);

		$this->assertEquals($expected, $modSetting);
	}

	public function modSettingProvider(): array
	{
		return [
			'modSetting exists' =>
			[
				'settingName' => 'CompressedOutput',
				'fallback' => false,
				'expected' => false,
			],
			'modSetting doesnt exists' =>
			[
				'settingName' => 'nope',
				'fallback' => 'Luffy',
				'expected' => 'Luffy',
			],
			'empty modSetting' =>
			[
				'settingName' => '',
				'fallback' => 'Nami',
				'expected' => 'Nami',
			],
		];
	}

	/**
	 * @dataProvider globalProvider
	 */
	public function testGlobal(string $globalName, $expected): void
	{
		$global = $this->settingsTrait->global($globalName);

		$this->assertSame($expected, $global);
	}

	public function globalProvider(): array
	{
		return [
			'global exists' =>
			[
				'globalName' => 'context',
				'expected' => [
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
				],
			],
			'global doesnt exists' =>
			[
				'globalName' => 'Invader Zim',
				'expected' => false,
			],
		];
	}
}
