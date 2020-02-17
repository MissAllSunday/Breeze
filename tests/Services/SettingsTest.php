<?php

declare(strict_types=1);

use Breeze\Service\Settings as SettingsService;
use PHPUnit\Framework\TestCase;

final class SettingsTest extends TestCase
{
	/**
	 * @var SettingsService
	 */
	private $settingsService;

	protected function setUp(): void
	{
		$this->settingsService = new SettingsService();
	}

	/**
	 * @dataProvider getProvider
	 */
	public function testGet(string $settingName, $fallBack, $expected): void
	{
		$setting = $this->settingsService->get($settingName, $fallBack);

		$this->assertEquals($expected, $setting);
	}

	public function getProvider(): array
	{
		return [
		    'string exists' =>
		    [
		        'settingName' => 'someSetting',
		        'fallback' => false,
		        'expected' => 666
		    ],
		    'use fallback' =>
		    [
		        'settingName' => 'nope',
		        'fallback' => 'Luffy',
		        'expected' => 'Luffy'
		    ],
		    'empty setting name' =>
		    [
		        'settingName' => '',
		        'fallback' => 'Nami',
		        'expected' => 'Nami'
		    ],
		];
	}

	/**
	 * @dataProvider enableProvider
	 */
	public function testEnable(string $settingName, bool $expected): void
	{
		$enable = $this->settingsService->enable($settingName);

		$this->assertIsBool($expected);
		$this->assertEquals($expected, $enable);
	}

	public function enableProvider(): array
	{
		return [
		    'setting enable' =>
		    [
		        'settingName' => 'master',
		        'expected' => true
		    ],
		    'setting disabled' =>
		    [
		        'settingName' => 'time_machine',
		        'expected' => false
		    ],
		];
	}

	/**
	 * @dataProvider modSettingProvider
	 */
	public function testModSetting(string $settingName, $fallBack, $expected): void
	{
		$modSetting = $this->settingsService->modSetting($settingName, $fallBack);

		$this->assertEquals($expected, $modSetting);
	}

	public function modSettingProvider(): array
	{
		return [
		    'modSetting exists' =>
		    [
		        'settingName' => 'CompressedOutput',
		        'fallback' => false,
		        'expected' => false
		    ],
		    'modSetting doesnt exists' =>
		    [
		        'settingName' => 'nope',
		        'fallback' => 'Luffy',
		        'expected' => 'Luffy'
		    ],
		    'empty modSetting' =>
		    [
		        'settingName' => '',
		        'fallback' => 'Nami',
		        'expected' => 'Nami'
		    ],
		];
	}

	/**
	 * @dataProvider isJsonProvider
	 */
	public function testIsJson($json, $expected): void
	{
		$isJson = $this->settingsService->isJson($json);

		$this->assertEquals( $expected, $isJson);
	}

	public function isJsonProvider(): array
	{
		return [
		    'is json' =>
		    [
		        'json' => json_encode(['Ace', 'Luffy', 'Sabo']),
		        'expected' => true
		    ],
		    'is not json' =>
		    [
		        'json' => 'Im Jason!',
		        'expected' => false
		    ],
		    'empty json' =>
		    [
		        'json' => json_encode([]),
		        'expected' => true
		    ],
		];
	}

	/**
	 * @dataProvider globalProvider
	 */
	public function testGlobal(string $globalName, $expected): void
	{
		$global = $this->settingsService->global($globalName);

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
		        ]
		    ],
		    'global doesnt exists' =>
		    [
		        'globalName' => 'Invader Zim',
		        'expected' => false
		    ]
		];
	}
}
