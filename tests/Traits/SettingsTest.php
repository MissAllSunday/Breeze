<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Breeze\Traits\SettingsTrait as SettingsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SettingsTest extends TestCase
{
	use SettingsTrait;

	#[DataProvider('getSettingProvider')]
	public function testGetSetting(string $settingName, $fallBack, $expected): void
	{
		$setting = $this->getSetting($settingName, $fallBack);

		$this->assertEquals($expected, $setting);
	}

	public static function getSettingProvider(): array
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

	#[DataProvider('enableProvider')]
	public function testEnable(string $settingName, bool $expected): void
	{
		$enable = $this->isEnable($settingName);

		$this->assertIsBool($expected);
		$this->assertEquals($expected, $enable);
	}

	public static function enableProvider(): array
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

	#[DataProvider('modSettingProvider')]
	public function testModSetting(string $settingName, $fallBack, $expected): void
	{
		$modSetting = $this->modSetting($settingName, $fallBack);

		$this->assertEquals($expected, $modSetting);
	}

	public static function modSettingProvider(): array
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

	#[DataProvider('globalProvider')]
	public function testGlobal(string $globalName, $expected): void
	{
		$global = $this->global($globalName);

		$this->assertSame($expected, $global);
	}

	public static function globalProvider(): array
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
