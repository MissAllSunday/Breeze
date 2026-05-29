<?php

declare(strict_types=1);

namespace Breeze\Traits;

use Breeze\Traits\SettingsTrait as SettingsTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SettingsTest extends TestCase
{
	use SettingsTrait;

	protected function setUp(): void
	{
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
				'fallBack' => false,
				'expected' => 666,
			],
			'use fallBack' =>
			[
				'settingName' => 'nope',
				'fallBack' => 'Luffy',
				'expected' => 'Luffy',
			],
			'empty setting name' =>
			[
				'settingName' => '',
				'fallBack' => 'Nami',
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
				'fallBack' => false,
				'expected' => false,
			],
			'modSetting doesnt exists' =>
			[
				'settingName' => 'nope',
				'fallBack' => 'Luffy',
				'expected' => 'Luffy',
			],
			'empty modSetting' =>
			[
				'settingName' => '',
				'fallBack' => 'Nami',
				'expected' => 'Nami',
			],
		];
	}

	public function testSetContextVarsMergesIntoExistingContext(): void
	{
		$this->setContextVars(['page_title' => 'My Wall']);

		$context = $this->global('context');

		$this->assertSame('My Wall', $context['page_title']);
		// Pre-existing keys must not be wiped out.
		$this->assertSame('foo', $context['session_var']);
	}

	public function testSetContextVarsDoesNotOverwriteLinktree(): void
	{
		$GLOBALS['context']['linktree'] = [['url' => 'http://example.com', 'name' => 'Home']];

		$this->setContextVars(['page_title' => 'Test']);

		$context = $this->global('context');
		$this->assertCount(1, $context['linktree']);
	}

	public function testAppendLinktreeAddsEntry(): void
	{
		$this->appendLinktree('http://example.com/?action=wall', 'Wall');

		$context = $this->global('context');

		$this->assertCount(1, $context['linktree']);
		$this->assertSame('http://example.com/?action=wall', $context['linktree'][0]['url']);
		$this->assertSame('Wall', $context['linktree'][0]['name']);
	}

	public function testAppendLinktreePreservesExistingEntries(): void
	{
		$GLOBALS['context']['linktree'] = [['url' => 'http://example.com', 'name' => 'Home']];

		$this->appendLinktree('http://example.com/?action=wall', 'Wall');

		$context = $this->global('context');

		$this->assertCount(2, $context['linktree']);
		$this->assertSame('Home', $context['linktree'][0]['name']);
		$this->assertSame('Wall', $context['linktree'][1]['name']);
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
